# Python Analytics Integration

## Purpose

ClearPay uses Python as a supporting analytics worker for the localhost web app.

CodeIgniter still owns:

- routing
- authentication
- database access
- page rendering
- file downloads

Python is used for:

- financial summaries
- payment trend analysis
- duplicate record detection
- suspicious record detection
- report generation for `pdf`, `csv`, and Excel-compatible `xls`

This keeps the main web app in PHP while moving analytics and report-building logic into one separate scripting layer.

## Current Integration Flow

The active integration path is:

`Admin Controller -> PythonAnalyticsService -> analytics/clearpay_analytics.py -> result back to PHP`

Main files:

- [PythonAnalyticsService.php](/d:/xampp/htdocs/ClearPay/app/Services/PythonAnalyticsService.php)
- [Analytics.php](/d:/xampp/htdocs/ClearPay/app/Controllers/Admin/Analytics.php)
- [ReviewCenterController.php](/d:/xampp/htdocs/ClearPay/app/Controllers/Admin/ReviewCenterController.php)
- [clearpay_analytics.py](/d:/xampp/htdocs/ClearPay/analytics/clearpay_analytics.py)

## Where Python Is Used

### 1. Analytics dashboard

When the admin opens `/analytics`, [Analytics.php](/d:/xampp/htdocs/ClearPay/app/Controllers/Admin/Analytics.php) calls:

```php
$analysis = $this->pythonAnalyticsService->generateAnalytics();
```

That returns analytics JSON which is passed into the analytics view.

### 2. Review Center alerts

When the admin opens `/review-center`, [ReviewCenterController.php](/d:/xampp/htdocs/ClearPay/app/Controllers/Admin/ReviewCenterController.php) also calls the Python service.

It uses the Python output to show:

- duplicate alerts
- suspicious alerts

### 3. Analytics export

When the admin downloads an analytics report from `/admin/analytics/export/{pdf|csv|excel}`, [Analytics.php](/d:/xampp/htdocs/ClearPay/app/Controllers/Admin/Analytics.php) calls:

```php
$report = $this->pythonAnalyticsService->generateReport($type);
```

Python generates the file, then PHP returns it as a normal download response.

## How PHP Invokes Python

[PythonAnalyticsService.php](/d:/xampp/htdocs/ClearPay/app/Services/PythonAnalyticsService.php) is the bridge between CodeIgniter and the Python worker.

It does five things:

1. finds a runnable Python executable
2. queries the database and builds a JSON payload
3. writes that payload to a temporary file
4. runs `clearpay_analytics.py` with CLI arguments
5. reads back either JSON output or a generated report file

The payload currently contains:

- `generated_at`
- `payments`
- `contributions`

The payment rows are queried with payer and contribution joins, so Python receives analytics-ready records such as:

- payer name
- payer id number
- profile picture
- contribution title
- contribution amount
- cost price
- payment amount
- payment method
- payment status
- receipt number
- payment sequence
- created/payment dates

## Python Executable Resolution

The PHP service looks for Python in this order:

1. `PYTHON_ANALYTICS_EXECUTABLE`
2. `analytics/.venv/Scripts/python.exe`
3. `analytics/.venv/bin/python`
4. `C:\Program Files\PostgreSQL\18\pgAdmin 4\python\python.exe`
5. `python` from `PATH`

Recommended local setup:

- install a real standalone Python
- set `PYTHON_ANALYTICS_EXECUTABLE` in `.env`

Example:

```dotenv
PYTHON_ANALYTICS_EXECUTABLE = "C:\Python313\python.exe"
```

## Python Worker Inputs And Outputs

The worker supports two CLI modes.

### Analyze mode

Command shape:

```bash
python analytics/clearpay_analytics.py analyze --input payload.json --output output.json
```

Result:

- writes structured analytics JSON

Used by:

- analytics page
- review center

### Report mode

Command shape:

```bash
python analytics/clearpay_analytics.py report --input payload.json --output report.pdf --format pdf
```

Supported formats:

- `pdf`
- `csv`
- `excel`

Result:

- writes a downloadable report file

Used by:

- analytics export endpoint

## What The Python Worker Does

`analytics/clearpay_analytics.py` runs in these stages:

### 1. Normalize incoming payload

The JSON payload from PHP contains strings and raw DB values.

The worker converts them into typed fields:

- numbers become `float` or `int`
- timestamps become `datetime`
- missing values are normalized safely

This happens in `normalize_payload()`.

### 2. Build core analytics

The main function is `analyze_payload()`.

It calculates:

- total revenue
- total contributions
- active contributors
- monthly revenue
- monthly growth
- total profit
- average profit margin
- total outstanding balance
- average transaction
- recent payments
- top payers
- top profitable contributions
- category breakdown

### 3. Detect duplicate records

`detect_duplicates()` flags rows using two rules:

- same receipt number used more than once
- same payer + contribution + amount + method + day pattern repeated

This is what feeds the duplicate alerts on the analytics page and review center.

### 4. Detect suspicious records

`detect_suspicious()` adds heuristics for records that look unusual, including:

- unusually large payment amounts
- very frequent same-day payments by one payer
- records that also match duplicate patterns
- non-cash payments with no receipt number

These are review flags, not automatic fraud decisions.

### 5. Build trend data

`build_trends()` creates:

- daily revenue for the last 30 days
- monthly revenue for the last 12 months
- daily transaction counts

It also creates `labels/data` arrays that the Chart.js frontend can use directly.

### 6. Generate export files

For report downloads, Python can write:

- CSV via `write_delimited_report(..., ",")`
- Excel-compatible tab-delimited output via `write_delimited_report(..., "\t")`
- a simple dependency-free PDF via `write_simple_pdf()`

## Why The Worker Uses Only The Standard Library

The Python worker intentionally avoids third-party packages.

That means:

- easier local setup
- no `pip install` requirement for analytics to run
- fewer deployment dependencies

Tradeoff:

- the PDF generation is intentionally simple
- analytics logic is custom rather than using pandas or numpy

## Error Handling

If Python fails:

- `PythonAnalyticsService` throws a `RuntimeException`
- the Review Center catches that and shows analytics as unavailable
- the app logs the failure for troubleshooting

Common failure points:

- Python executable path is wrong
- worker script is missing
- temp files cannot be created in `writable/cache`
- malformed payload or command failure

## Localhost Notes

For localhost web work, the important requirement is that the configured Python executable must actually run on the machine.

Quick check:

```bash
"C:\Path\To\Python\python.exe" --version
```

If the worker path is correct, these web features should work:

- `/analytics`
- `/review-center`
- `/admin/analytics/export/pdf`
- `/admin/analytics/export/csv`
- `/admin/analytics/export/excel`

## Current Design Boundary

Python is currently an internal worker, not a separate web service.

That means:

- no Flask/FastAPI server
- no HTTP calls from PHP to Python
- no background queue

The integration is process-based:

1. PHP builds payload
2. PHP runs Python
3. Python writes output
4. PHP reads output
5. PHP renders or downloads it

## Summary

In the current ClearPay web app:

- PHP remains the main application layer
- Python is the analytics and report-generation worker
- the integration is file-and-process based
- analytics dashboard, review center alerts, and report exports all depend on the same Python worker
