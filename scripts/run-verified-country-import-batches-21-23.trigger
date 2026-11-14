2026-11-14
Verified BGD ordinary-passport tourism batches 21, 22 and 23
Allowlist: 44 backlog + KY BM PA MX + BO CU HT
Pending NI and HN excluded

Retry after schema-limit fixes: CK/TK/AL/IE/ME/RS/PA entry_count and BJ fee summary shortened; preflight validates field lengths. Serialized with production deployment.

Retry after correcting effective_from: policy effective date not established for BO/CU/HT; leave null, retain checked dates in source metadata. Idempotent readback verification required.

Retry after production workflow serialization change; deployment workflow now queues rather than cancelling this verified import.
