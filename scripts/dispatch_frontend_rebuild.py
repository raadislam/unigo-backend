import json
import os
import sys
import time
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

token = os.environ.get("UNIGO_FRONTEND_DISPATCH_TOKEN", "").strip()
if not token:
    raise SystemExit("UNIGO_FRONTEND_DISPATCH_TOKEN is required to rebuild and verify the static frontend.")

base = "https://api.github.com/repos/raadislam/unigo-frontend"
headers = {
    "Accept": "application/vnd.github+json",
    "Authorization": f"Bearer {token}",
    "X-GitHub-Api-Version": "2022-11-28",
    "User-Agent": "UniGo-Visa-Directory-Release",
}

def api(method, path, payload=None):
    data = None if payload is None else json.dumps(payload).encode("utf-8")
    request = Request(base + path, data=data, headers=headers, method=method)
    try:
        with urlopen(request, timeout=45) as response:
            body = response.read()
            return response.status, json.loads(body.decode("utf-8")) if body else {}
    except HTTPError as error:
        raise RuntimeError(f"GitHub API returned HTTP {error.code} for {path}") from error
    except URLError as error:
        raise RuntimeError(f"GitHub API request failed for {path}: {error.reason}") from error

_, before_payload = api("GET", "/actions/runs?event=repository_dispatch&per_page=20")
before_ids = {run["id"] for run in before_payload.get("workflow_runs", [])}
api("POST", "/dispatches", {"event_type": "visa-content-updated"})

deadline = time.time() + 900
while time.time() < deadline:
    _, payload = api("GET", "/actions/runs?event=repository_dispatch&per_page=20")
    candidates = [
        run for run in payload.get("workflow_runs", [])
        if run.get("id") not in before_ids and run.get("head_branch") in {"master", "main"}
    ]
    if candidates:
        run = max(candidates, key=lambda item: item.get("id", 0))
        if run.get("status") == "completed":
            if run.get("conclusion") != "success":
                raise SystemExit(f"Frontend deployment verification failed: run {run.get('id')} concluded {run.get('conclusion')}.")
            print(f"Frontend static export and live parity workflow succeeded: run {run.get('id')}.")
            sys.exit(0)
    time.sleep(15)

raise SystemExit("Timed out waiting for the frontend repository_dispatch deployment and parity checks.")
