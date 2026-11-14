GitHub Actions recovery note

The optional legacy fee and processing-time CSV validation is isolated from the main deployment. If the CSV cannot be validated against current production Bangladesh tourism records, its import is skipped and the deployment continues without changing those fee records. The data batch must be reconciled separately before import.
