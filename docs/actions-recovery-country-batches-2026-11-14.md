Country research batch recovery

Optional research CSV batches are validated independently. A batch that fails dry-run validation is skipped and must be reconciled against its missing country records or source data before it can be imported. Core application deployment, checklist enrichment, and production audit continue independently. Failed batch data is never force-published.
