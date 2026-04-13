# ClearPay Setup Guide For Groupmates (Beginner Friendly)

Use this exact guide if you are setting up ClearPay on a new laptop/PC for the first time.

## 1. What you need to install first

1. Install **XAMPP** (Apache + MySQL + PHP).
2. Install **Git**.
3. Install **Composer**.

After installing, restart your computer once.

## 2. Start required services

1. Open **XAMPP Control Panel**.
2. Click **Start** for:
- `Apache`
- `MySQL`
3. Keep XAMPP running while testing.

## 3. Download the project from GitHub

Open **PowerShell** and run:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/DonDon14/clearpay.git ClearPay
cd ClearPay
composer install
```

## 4. Create the `.env` file

1. Inside `C:\xampp\htdocs\ClearPay`, copy `.env.example` and rename the copy to `.env`.
2. Open `.env` in VS Code.
3. Make sure these values are set:

```dotenv
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
database.default.DBPrefix =

email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPUser = YOUR_EMAIL@gmail.com
email.SMTPPass = YOUR_APP_PASSWORD
email.SMTPPort = 587
email.SMTPCrypto = tls
email.fromEmail = YOUR_EMAIL@gmail.com
email.fromName = ClearPay
```

Important:
- If you use Gmail, `SMTPPass` must be a **Gmail App Password**, not your Gmail login password.

## 5. Create the database (phpMyAdmin)

1. Open browser: `http://localhost/phpmyadmin`
2. Click **New** (left side).
3. Database name: `clearpaydb`
4. Click **Create**.

## 6. Run database migrations

Back in PowerShell (inside `C:\xampp\htdocs\ClearPay`), run:

```powershell
php spark migrate --all
```

If your team has a seeder for initial accounts, run it too (optional):

```powershell
php spark db:seed InitialAdminSeeder
```

## 7. Run the app

In PowerShell, run:

```powershell
php spark serve
```

Open in browser:
- `http://localhost:8080`

## 8. Login and basic test flow

1. Login as Admin.
2. Open `Contributions` and `Products` pages to confirm data loads.
3. Test Payer signup.
4. Test email verification code.
5. Test Payer forgot password.
6. Test Admin payment save.
7. Test Admin refund flow.

## 9. Automated test check (recommended)

In the same project folder:

```powershell
vendor\bin\phpunit --testdox
```

Expected result:
- `OK (15 tests, 70 assertions)`

## 10. If it does not work, use this quick fix list

1. Error: `Could not open input file: spark`
- You are in the wrong folder.
- Run:
```powershell
cd C:\xampp\htdocs\ClearPay
```

2. Error: database/table missing
- Confirm MySQL is running in XAMPP.
- Re-run:
```powershell
php spark migrate --all
```

3. App loads but emails are not sent
- Recheck `.env` email values.
- Use valid Gmail App Password.

4. Port already in use on `8080`
- Run:
```powershell
php spark serve --port 8081
```
- Open: `http://localhost:8081`

## 11. Optional: let another device on same Wi-Fi open your running app

Run:

```powershell
php spark serve --host 0.0.0.0 --port 8080
```

Find your IP:

```powershell
ipconfig
```

Use this from another device browser:
- `http://YOUR_LOCAL_IP:8080`

If blocked, allow port `8080` in Windows Firewall.
