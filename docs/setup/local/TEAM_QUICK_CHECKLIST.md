# ClearPay Team Quick Checklist

Use this if you want the fastest setup flow.

## A. One-time install

1. Install XAMPP
2. Install Git
3. Install Composer

## B. Start services

1. Open XAMPP
2. Start `Apache`
3. Start `MySQL`

## C. Download project

```powershell
cd C:\xampp\htdocs
git clone https://github.com/DonDon14/clearpay.git ClearPay
cd ClearPay
composer install
```

## D. Create `.env`

1. Copy `.env.example` to `.env`
2. Set DB:
- `database.default.database = clearpaydb`
- `database.default.username = root`
- `database.default.password =`
3. Set app URL:
- `app.baseURL = 'http://localhost:8080/'`
4. Set SMTP email values

## E. Create DB + migrate

1. Open `http://localhost/phpmyadmin`
2. Create database: `clearpaydb`
3. Run:

```powershell
php spark migrate --all
```

## F. Run app

```powershell
php spark serve
```

Open:
- `http://localhost:8080`

## G. Run tests

```powershell
vendor\bin\phpunit --testdox
```

Expected:
- `OK (15 tests, 70 assertions)`

## H. Quick manual checks

1. Admin login works
2. Payer signup works
3. Email verification works
4. Forgot password works
5. Payment save works
6. Refund process works

## I. If error appears

1. `Could not open input file: spark`
- Go to project folder:
```powershell
cd C:\xampp\htdocs\ClearPay
```

2. DB/table missing
- Run migrations again:
```powershell
php spark migrate --all
```

3. Email not sending
- Recheck SMTP values in `.env`
