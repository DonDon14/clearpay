# ClearPay Repo Copy And Code Review Guide

Use this guide to copy the ClearPay repository to your laptop, review the code, and prepare for defense questions.

This guide has two setup levels:

1. **Code review only**: fastest. You can read and search the code in VS Code.
2. **Run locally**: slower. They install dependencies, configure `.env`, create a database, and run the app.

For defense preparation, code review only is usually enough unless you need to test features by yourself.

## 1. Repository URL

GitHub repository:

```text
https://github.com/DonDon14/clearpay.git
```

Recommended local folder:

```text
C:\xampp\htdocs\ClearPay
```

## 2. Install Required Tools

Install these tools:

| Tool | Purpose |
| --- | --- |
| Git | Downloads/clones the repository. |
| VS Code | Reads and searches the code. |
| XAMPP | Provides PHP/Apache for local testing. |
| Composer | Installs PHP dependencies into `vendor/`. |
| PostgreSQL or MySQL/MariaDB | Database engine if they want to run the app locally. |

For code review only, you only need **Git** and **VS Code**.

For running the app locally, you need **XAMPP**, **Composer**, and a database too.

## 3. Option A: Code Review Only

This is the easiest option if you only need to study the code.

Open PowerShell:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/DonDon14/clearpay.git ClearPay
cd ClearPay
code .
```

If `code .` does not work, open VS Code manually, then choose:

```text
File -> Open Folder -> C:\xampp\htdocs\ClearPay
```

You can now inspect the project without database setup.

## 4. Recommended Files To Review First

Start with these files in this order:

| Purpose | File |
| --- | --- |
| All URL mappings | `app/Config/Routes.php` |
| Admin login and forgot password | `app/Controllers/Admin/LoginController.php` |
| Payer login and forgot password | `app/Controllers/Payer/LoginController.php` |
| Payer signup and email verification | `app/Controllers/Payer/SignupController.php` |
| Payer dashboard, payment request, refund request | `app/Controllers/Payer/DashboardController.php` |
| Manual payments | `app/Controllers/Admin/PaymentsController.php` |
| Admin payment request approval/rejection | `app/Controllers/Admin/DashboardController.php` |
| Refund management | `app/Controllers/Admin/RefundsController.php` |
| Python analytics PHP bridge | `app/Services/PythonAnalyticsService.php` |
| Python analytics worker | `analytics/clearpay_analytics.py` |
| Shared email sender | `app/Services/EmailDeliveryService.php` |
| Admin route guard | `app/Filters/Auth.php` |

## 5. How To Search The Code In VS Code

Use:

```text
Ctrl + Shift + F
```

Search examples:

```text
loginPost
signupPost
submitPaymentRequest
submitRefundRequest
approvePaymentRequest
completeRefund
sendReceiptEmail
PythonAnalyticsService
password_verify
password_hash
```

Use this answer if an instructor asks how to find code:

```text
We start from Routes.php to find the URL, then open the controller method, then check the model or service used by that method.
```

## 6. Option B: Install Dependencies For Better Code Review

This helps VS Code understand PHP classes better.

From the project folder:

```powershell
cd C:\xampp\htdocs\ClearPay
composer install
```

This creates the `vendor/` folder. It is required if they want to run `php spark` commands.

## 7. Option C: Run The App Locally

Only do this if you want to test the app on your own laptop.

### 7.1 Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

If you use PostgreSQL instead, start PostgreSQL from Windows Services or pgAdmin.

### 7.2 Create `.env`

Inside:

```text
C:\xampp\htdocs\ClearPay
```

Copy one of these files and rename it to `.env`:

```text
.env.example
.env.example.postgresql
```

Recommended for easiest local setup with XAMPP MySQL/MariaDB:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.DBDebug = true
```

If using PostgreSQL, use:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = postgres
database.default.password = YOUR_POSTGRES_PASSWORD
database.default.DBDriver = Postgre
database.default.DBPrefix =
database.default.port = 5432
database.default.DBDebug = true
```

Important:

- Do not copy private production/demo passwords into another laptop unless the group agrees.
- For code review, email credentials are not needed.
- For testing email, use your own Gmail App Password or skip email sending.

### 7.3 Create Database

For XAMPP MySQL/MariaDB:

1. Open:

```text
http://localhost/phpmyadmin
```

2. Click **New**.
3. Database name:

```text
clearpaydb
```

4. Click **Create**.

For PostgreSQL:

Create a database named:

```text
clearpaydb
```

using pgAdmin or `psql`.

### 7.4 Generate App Key

From the project folder:

```powershell
php spark key:generate
```

### 7.5 Run Migrations And Seeders

From the project folder:

```powershell
php spark migrate
php spark db:seed DatabaseSeeder
php spark db:seed PaymentMethodSeeder
```

If a migration says it already ran, that is usually okay.

### 7.6 Verify Setup

Run:

```powershell
php spark setup:verify
```

Expected result:

```text
No blocking setup errors.
```

Warnings about Brevo or Cloudinary are okay for local code review/demo testing.

### 7.7 Run Local Server

Run:

```powershell
php spark serve
```

Open:

```text
http://localhost:8080
```

If port `8080` is busy:

```powershell
php spark serve --port 8081
```

Then open:

```text
http://localhost:8081
```

## 8. Pull Latest Changes Later

If the repo is already cloned and you want the newest code:

```powershell
cd C:\xampp\htdocs\ClearPay
git pull origin main
composer install
```

If the app database changed, also run:

```powershell
php spark migrate
php spark db:seed DatabaseSeeder
```

## 9. Important Folders To Understand

| Folder | Meaning |
| --- | --- |
| `app/Config` | Routes, filters, and app/database/email config. |
| `app/Controllers` | Workflow logic for admin, payer, and super admin. |
| `app/Models` | Database access classes. |
| `app/Services` | Shared services such as email and Python analytics. |
| `app/Views` | HTML/PHP templates shown in browser or email. |
| `analytics` | Python analytics worker. |
| `public` | Web entrypoint, CSS, JS, and uploaded files. |
| `docs` | Defense, setup, deployment, and troubleshooting documentation. |
| `tests` | Automated tests. |

## 10. Common Problems And Fixes

### Problem: `git` is not recognized

Install Git, then restart PowerShell.

### Problem: `composer` is not recognized

Install Composer, then restart PowerShell.

### Problem: `Could not open input file: spark`

You are in the wrong folder.

Fix:

```powershell
cd C:\xampp\htdocs\ClearPay
```

### Problem: database connection failed

Check `.env` database settings and confirm MySQL/PostgreSQL is running.

### Problem: table does not exist

Run:

```powershell
php spark migrate
php spark db:seed DatabaseSeeder
```

### Problem: payment methods are missing

Run:

```powershell
php spark db:seed PaymentMethodSeeder
```

### Problem: app opens but CSS/JS is broken

Check `.env`:

```dotenv
app.baseURL = 'http://localhost:8080/'
```

Make sure they are opening the same URL:

```text
http://localhost:8080
```

### Problem: email does not send

For code review, ignore email sending.

For local email testing:

```powershell
php spark email:test your-email@example.com
```

If it fails, check SMTP settings and Gmail App Password.

## 11. Recommended Defense Practice Flow

Practice explaining one workflow:

1. Open `app/Config/Routes.php`.
2. Find the route.
3. Open the controller method.
4. Identify the model or service it uses.
5. Explain the database effect.
6. Explain the response shown to the user.

Example:

```text
For payer payment request:
Route: payer/submit-payment-request
Controller: Payer\DashboardController::submitPaymentRequest()
Model: PaymentRequestModel
Database effect: creates pending payment request
Admin continuation: Admin\DashboardController approves/processes it
Final result: official payment row is created or request is rejected
```

## 12. What They Should Not Worry About

For code review and defense preparation, you do not need:

- Flutter/mobile app setup.
- Ngrok setup.
- Cloudinary setup.
- Brevo setup.
- Production hosting setup.

Those are optional for deployment/demo, not required for reviewing the source code.

## 13. Fastest Way For The Group To Review Together

The simplest setup is:

1. One laptop runs the working demo through ngrok.
2. Everyone else clones the repo for code reading.
3. Everyone opens the same Google Docs defense document.
4. Each member follows their assigned workflow and code files.

This avoids wasting time debugging multiple local database setups before defense.
