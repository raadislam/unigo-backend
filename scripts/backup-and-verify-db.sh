#!/usr/bin/env bash
set -Eeuo pipefail
set +x

APP_DIR="$1"
RUN_ID="$2"
RUN_ATTEMPT="$3"

if [ -z "$APP_DIR" ] || [ -z "$RUN_ID" ] || [ -z "$RUN_ATTEMPT" ]; then
  printf '%s\n' "Backup preflight arguments are incomplete." >&2
  exit 1
fi

cd "$APP_DIR"
test -f vendor/autoload.php
test -f .env
command -v php >/dev/null
command -v gzip >/dev/null
command -v sha256sum >/dev/null
MYSQL_BIN="$(command -v mysql || command -v mariadb || true)"
DUMP_BIN="$(command -v mysqldump || command -v mariadb-dump || true)"
if [ -z "$MYSQL_BIN" ] || [ -z "$DUMP_BIN" ]; then
  printf '%s\n' "MySQL-compatible client and dump tools are required for backup verification." >&2
  exit 1
fi

BACKUP_DIR="$(php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo storage_path("app/backups");')"
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"
umask 077

STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
TMP_BACKUP_PATH="$BACKUP_DIR/.unigo-db-$RUN_ID-$RUN_ATTEMPT-$STAMP.pending.sql.gz"
BACKUP_PATH="$BACKUP_DIR/unigo-db-$RUN_ID-$RUN_ATTEMPT-$STAMP.sql.gz"
MANIFEST_PATH="$BACKUP_PATH.manifest.json"
CRED_FILE="$(mktemp /tmp/unigo-db-client.XXXXXX.cnf)"
META_FILE="$(mktemp /tmp/unigo-db-meta.XXXXXX)"
TEST_DB="unigo_restore_test_${RUN_ID}_${RUN_ATTEMPT}"
TEST_DB_CREATED=0

cleanup() {
  status=$?
  trap - EXIT
  if [ "$TEST_DB_CREATED" = "1" ]; then
    if ! "$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -e "DROP DATABASE \`$TEST_DB\`" >/dev/null 2>&1; then
      printf '%s\n' "Unable to remove the isolated restore-test database; production database was not modified." >&2
      status=1
    fi
  fi
  rm -f "$CRED_FILE" "$META_FILE" "$TMP_BACKUP_PATH"
  exit "$status"
}
trap cleanup EXIT
trap 'exit 130' INT TERM

export UNIGO_DB_CREDENTIALS_FILE="$CRED_FILE"
export UNIGO_DB_META_FILE="$META_FILE"

php <<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = Illuminate\Support\Facades\DB::connection();
$config = $connection->getConfig();
if (($config['driver'] ?? '') !== 'mysql') {
    fwrite(STDERR, "Production database driver is not MySQL-compatible; backup preflight stopped.\n");
    exit(1);
}
$database = $connection->getDatabaseName();
if (!is_string($database) || !preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    fwrite(STDERR, "Production database name is not supported by the safe backup preflight.\n");
    exit(1);
}
$escape = static function ($value): string {
    return '"' . str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', "\\n", "\\r"], (string) $value) . '"';
};
$client = "[client]\n";
$client .= "user=" . $escape($config['username'] ?? '') . "\n";
$client .= "password=" . $escape($config['password'] ?? '') . "\n";
if (!empty($config['unix_socket'])) {
    $client .= "socket=" . $escape($config['unix_socket']) . "\n";
} else {
    $client .= "host=" . $escape($config['host'] ?? '127.0.0.1') . "\n";
    $client .= "port=" . (int) ($config['port'] ?? 3306) . "\n";
}
if (file_put_contents(getenv('UNIGO_DB_CREDENTIALS_FILE'), $client, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write protected database client configuration.\n");
    exit(1);
}
chmod(getenv('UNIGO_DB_CREDENTIALS_FILE'), 0600);
$schema = $connection->selectOne(
    'SELECT DEFAULT_CHARACTER_SET_NAME AS charset_name, DEFAULT_COLLATION_NAME AS collation_name FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
    [$database]
);
if (!$schema || !preg_match('/^[A-Za-z0-9_]+$/', $schema->charset_name) || !preg_match('/^[A-Za-z0-9_]+$/', $schema->collation_name)) {
    fwrite(STDERR, "Unable to verify production database character set and collation.\n");
    exit(1);
}
file_put_contents(
    getenv('UNIGO_DB_META_FILE'),
    implode("\n", [$database, $schema->charset_name, $schema->collation_name]),
    LOCK_EX
);
PHP

read -r DB_NAME DB_CHARSET DB_COLLATION < <(paste -sd ' ' "$META_FILE")
if [ -z "$DB_NAME" ] || [ -z "$DB_CHARSET" ] || [ -z "$DB_COLLATION" ]; then
  printf '%s\n' "Database metadata is incomplete." >&2
  exit 1
fi
if ! [[ "$DB_NAME" =~ ^[A-Za-z0-9_]+$ && "$DB_CHARSET" =~ ^[A-Za-z0-9_]+$ && "$DB_COLLATION" =~ ^[A-Za-z0-9_]+$ ]]; then
  printf '%s\n' "Database metadata contains unsupported identifiers." >&2
  exit 1
fi
if [ -e "$TMP_BACKUP_PATH" ] || [ -e "$BACKUP_PATH" ] || [ -e "$MANIFEST_PATH" ]; then
  printf '%s\n' "A backup with the generated path already exists." >&2
  exit 1
fi

NON_INNODB="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_TYPE = 'BASE TABLE' AND ENGINE <> 'InnoDB'")"
if [ "$NON_INNODB" != "0" ]; then
  printf '%s\n' "Non-InnoDB tables are present; refusing a non-locking single-transaction backup." >&2
  exit 1
fi

DUMP_ARGS=(--defaults-extra-file="$CRED_FILE" --single-transaction --skip-lock-tables --routines --triggers --events --hex-blob --quick)
if "$DUMP_BIN" --help 2>/dev/null | grep -q -- '--no-tablespaces'; then
  DUMP_ARGS+=(--no-tablespaces)
fi
if "$DUMP_BIN" --help 2>/dev/null | grep -q -- '--set-gtid-purged'; then
  DUMP_ARGS+=(--set-gtid-purged=OFF)
fi

"$DUMP_BIN" "${DUMP_ARGS[@]}" "$DB_NAME" | gzip -c > "$TMP_BACKUP_PATH"
test -s "$TMP_BACKUP_PATH"
gzip -t "$TMP_BACKUP_PATH"

EXISTING_TEST_DB="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '$TEST_DB'")"
if [ "$EXISTING_TEST_DB" != "0" ]; then
  printf '%s\n' "Isolated restore-test database name already exists; refusing to modify it." >&2
  exit 1
fi

"$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -e "CREATE DATABASE \`$TEST_DB\` CHARACTER SET $DB_CHARSET COLLATE $DB_COLLATION"
TEST_DB_CREATED=1
gzip -dc "$TMP_BACKUP_PATH" | "$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" "$TEST_DB"

TABLES="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$DB_NAME' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME")"
if [ -z "$TABLES" ]; then
  printf '%s\n' "Production database has no base tables; refusing to accept an empty backup." >&2
  exit 1
fi

TABLE_COUNT=0
while IFS= read -r TABLE_NAME; do
  [ -n "$TABLE_NAME" ] || continue
  if ! [[ "$TABLE_NAME" =~ ^[A-Za-z0-9_]+$ ]]; then
    printf '%s\n' "Unsupported table identifier in production schema." >&2
    exit 1
  fi
  SOURCE_COUNT="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM \`$DB_NAME\`.\`$TABLE_NAME\`")"
  RESTORE_COUNT="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM \`$TEST_DB\`.\`$TABLE_NAME\`")"
  if [ "$SOURCE_COUNT" != "$RESTORE_COUNT" ]; then
    printf 'Restored row count mismatch for table %s.\n' "$TABLE_NAME" >&2
    exit 1
  fi
  TABLE_COUNT=$((TABLE_COUNT + 1))
done <<< "$TABLES"

SOURCE_VIEWS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = '$DB_NAME'")"
RESTORE_VIEWS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = '$TEST_DB'")"
SOURCE_COLUMNS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$DB_NAME'")"
RESTORE_COLUMNS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$TEST_DB'")"
SOURCE_INDEXES="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = '$DB_NAME'")"
RESTORE_INDEXES="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = '$TEST_DB'")"
SOURCE_TRIGGERS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = '$DB_NAME'")"
RESTORE_TRIGGERS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = '$TEST_DB'")"
SOURCE_ROUTINES="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '$DB_NAME'")"
RESTORE_ROUTINES="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = '$TEST_DB'")"
SOURCE_EVENTS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.EVENTS WHERE EVENT_SCHEMA = '$DB_NAME'")"
RESTORE_EVENTS="$("$MYSQL_BIN" --defaults-extra-file="$CRED_FILE" -Nse "SELECT COUNT(*) FROM information_schema.EVENTS WHERE EVENT_SCHEMA = '$TEST_DB'")"

if [ "$SOURCE_VIEWS" != "$RESTORE_VIEWS" ] || [ "$SOURCE_COLUMNS" != "$RESTORE_COLUMNS" ] || [ "$SOURCE_INDEXES" != "$RESTORE_INDEXES" ] || [ "$SOURCE_TRIGGERS" != "$RESTORE_TRIGGERS" ] || [ "$SOURCE_ROUTINES" != "$RESTORE_ROUTINES" ] || [ "$SOURCE_EVENTS" != "$RESTORE_EVENTS" ]; then
  printf '%s\n' "Restored schema objects do not match production." >&2
  exit 1
fi

BACKUP_SHA="$(sha256sum "$TMP_BACKUP_PATH" | awk '{print $1}')"
BACKUP_SIZE="$(wc -c < "$TMP_BACKUP_PATH" | tr -d ' ')"
mv "$TMP_BACKUP_PATH" "$BACKUP_PATH"
chmod 600 "$BACKUP_PATH"

php -r '
$manifest = [
  "backup_path" => $argv[1],
  "sha256" => $argv[2],
  "size_bytes" => (int) $argv[3],
  "table_count" => (int) $argv[4],
  "column_count" => (int) $argv[5],
  "index_count" => (int) $argv[6],
  "view_count" => (int) $argv[7],
  "trigger_count" => (int) $argv[8],
  "routine_count" => (int) $argv[9],
  "event_count" => (int) $argv[10],
  "restore_test" => "passed",
  "checked_at_utc" => gmdate("c")
];
file_put_contents($argv[11], json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
' "$BACKUP_PATH" "$BACKUP_SHA" "$BACKUP_SIZE" "$TABLE_COUNT" "$SOURCE_COLUMNS" "$SOURCE_INDEXES" "$SOURCE_VIEWS" "$SOURCE_TRIGGERS" "$SOURCE_ROUTINES" "$SOURCE_EVENTS" "$MANIFEST_PATH"
chmod 600 "$MANIFEST_PATH"

printf 'Restorable production database backup verified: %s\n' "$BACKUP_PATH"
printf 'Restore test matched %s tables, %s columns, %s indexes, %s views, %s triggers, %s routines and %s events.\n' "$TABLE_COUNT" "$SOURCE_COLUMNS" "$SOURCE_INDEXES" "$SOURCE_VIEWS" "$SOURCE_TRIGGERS" "$SOURCE_ROUTINES" "$SOURCE_EVENTS"
