# ClearPay - Payment Management System

A comprehensive payment management system built with CodeIgniter 4, designed for managing contributions, payments, and payer information with a modern web interface.

Group Members:

   Floro C. Ocero, Jr.
   Michelle Miranda
   Jolina Ramao
   Tristan Amiel Clemen
   Mark Tristan Sicalag

## 🚀 Features

- **User Management**: Admin and officer roles with different permissions
- **Contribution Management**: Create and manage different types of contributions
- **Payment Processing**: Track payments with multiple payment methods
- **Payer Management**: Comprehensive payer information and payment history
- **Activity Logging**: Track all user activities and system changes
- **Announcements**: System-wide announcements and notifications
- **Refund Management**: Handle refunds and refund methods
- **QR Code Receipts**: Generate QR codes for payment receipts
- **Responsive Design**: Modern, mobile-friendly interface

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP 8.1 or higher** (with required extensions)
- **XAMPP** (Apache, MySQL, PHP)
- **Composer** (PHP dependency manager)
- **Git** (for cloning the repository)

### Required PHP Extensions

Make sure these PHP extensions are enabled:
- `intl`
- `mbstring`
- `json` (enabled by default)
- `mysqlnd` (for MySQL support)
- `libcurl` (for HTTP requests)

## 🛠️ Installation Guide

### Step 1: Install XAMPP

1. **Download XAMPP**
   - Visit [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
   - Download the latest version for your operating system
   - Choose the version with PHP 8.1 or higher

2. **Install XAMPP**
   - Run the installer as administrator
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   - Choose installation directory (default: `C:\xampp`)
   - Complete the installation

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services
   - Ensure both services are running (green status)

### Step 2: Install Composer

1. **Download Composer**
   - Visit [https://getcomposer.org/download/](https://getcomposer.org/download/)
   - Download and run `Composer-Setup.exe`

2. **Install Composer**
   - Follow the installation wizard
   - Make sure to check "Add this PHP installation to your PATH"
   - Complete the installation

3. **Verify Installation**
   - Open Command Prompt or PowerShell
   - Run: `composer --version`
   - You should see the Composer version information

### Step 3: Clone the Repository

1. **Open Command Prompt/PowerShell**
   - Navigate to your XAMPP htdocs directory:
   ```bash
   cd C:\xampp\htdocs
   ```

2. **Clone the Repository**
   ```bash
   git clone https://github.com/DonDon14/ClearPay.git
   ```

3. **Navigate to Project Directory**
   ```bash
   cd ClearPay
   ```

### Step 4: Install Dependencies

1. **Install PHP Dependencies**
   ```bash
   composer install
   ```

2. **Verify Installation**
   - Check that the `vendor` folder is created
   - Ensure no errors during installation

### Step 5: Database Setup

1. **Create Database**
   - Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Click "New" to create a new database
   - Database name: `clearpaydb`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

2. **Configure Database Connection**
   - The database configuration is already set in `app/Config/Database.php`
   - Default settings:
     - Host: `localhost`
     - Username: `root`
     - Password: (empty)
     - Database: `clearpaydb`
     - Port: `3306`

3. **Run Database Migrations**
   ```bash
   php spark migrate
   ```
   This creates all database tables.

4. **Seed the Database (CRITICAL - Do Not Skip!)**
   ```bash
   php spark db:seed DatabaseSeeder
   ```
   **IMPORTANT:** This seeds:
   - Admin user account
   - Sample contributions
   - **Payment methods (GCash, PayMaya, Bank Transfer, Cash, etc.)** ← Required for payment validation!
   
   **Without this step, payment creation will fail with validation errors!**

5. **Verify Payment Methods Were Seeded**
   ```bash
   # Check in phpMyAdmin or run:
   php spark db:table payment_methods
   ```
   You should see at least 4-5 payment methods. If empty, run:
   ```bash
   php spark db:seed PaymentMethodSeeder
   ```

### Step 6: Configure Application

1. **Set Base URL**
   - Open `app/Config/App.php`
   - Update the `$baseURL` if needed:
   ```php
   public string $baseURL = 'http://localhost/ClearPay/public/';
   ```

2. **Set File Permissions** (if on Linux/Mac)
   ```bash
   chmod -R 755 writable/
   chmod -R 755 public/uploads/
   ```

### Step 7: Access the Application

1. **Start XAMPP Services**
   - Ensure Apache and MySQL are running in XAMPP Control Panel

2. **Open Web Browser**
   - Navigate to: [http://localhost/ClearPay/public/](http://localhost/ClearPay/public/)

3. **Default Login Credentials**
   - **Admin Account:**
     - Username: `admin`
     - Email: `admin@example.com`
     - Password: `admin123`

## 🗄️ Database Structure

The application uses the following main tables:

- **users** - System users (admin/officer)
- **contributions** - Payment contributions/fees
- **payers** - Payer information and payment records
- **user_activities** - Activity logging
- **announcements** - System announcements
- **payment_methods** - Available payment methods
- **refunds** - Refund records
- **activity_logs** - Detailed activity tracking

## 🔧 Configuration

### Environment Configuration

The application uses CodeIgniter 4's configuration system. Key configuration files:

- `app/Config/App.php` - Application settings
- `app/Config/Database.php` - Database configuration
- `app/Config/Email.php` - Email settings

### .env Setup (not included in repo)

If `.env` is not present, create one in the project root. This minimal configuration works for XAMPP on Windows (adjust if your folder name or DB creds differ):

```dotenv
CI_ENVIRONMENT = production

app.baseURL = 'http://localhost/ClearPay/public/'
app.appTimezone = 'Asia/Manila'

# Security key (required for encryption/sessions)
encryption.key = base64:REPLACE_WITH_GENERATED_KEY

# Database
database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.DBDebug = true

# Email (optional; needed for password reset/verification)
# email.fromEmail = 'no-reply@example.com'
# email.fromName  = 'ClearPay'
# email.SMTPHost  = 'smtp.gmail.com'
# email.SMTPUser  = 'your@gmail.com'
# email.SMTPPass  = 'your-app-password'
# email.SMTPPort  = 587
# email.SMTPCrypto = 'tls'
```

Then:
- Generate a secure key: run `php spark key:generate` and copy the output into `encryption.key` (or let the command set it if supported).
- Start MySQL in XAMPP, then run `php spark migrate` and `php spark db:seed`.

### XAMPP directory placement and baseURL

- Place the project at `C:\xampp\htdocs\ClearPay`.
- Ensure `.env` `app.baseURL` matches your folder name:
  - If the folder is `C:\xampp\htdocs\ClearPay` → `http://localhost/ClearPay/public/`
  - If renamed to `ClearPay2` → `http://localhost/ClearPay2/public/`

Optional (cleaner URL): configure an Apache VirtualHost pointing to the `public` directory (e.g., `http://clearpay.local/`) and set `app.baseURL` accordingly. Otherwise, keeping `/public/` in the URL is fine.

### File Upload Configuration

Upload directories are configured in:
- `public/uploads/payment_proofs/` - Payment proof images
- `public/uploads/profile/` - User profile pictures
- `public/uploads/qr_receipts/` - QR code receipts

## 🚀 Development

### Running Migrations

```bash
# Run all migrations
php spark migrate

# Run specific migration
php spark migrate -g default

# Rollback migrations
php spark migrate:rollback
```

### Running Seeders

```bash
# Run all seeders (RECOMMENDED - includes PaymentMethodSeeder)
php spark db:seed DatabaseSeeder

# Run specific seeder
php spark db:seed UserSeeder
php spark db:seed PaymentMethodSeeder  # CRITICAL for payment validation!
```

### Verifying Setup

```bash
# Verify that setup is complete
php spark setup:verify
```

This checks:
- Database connection
- All required tables exist
- Payment methods are seeded (at least 4 active methods)
- Users exist
- Environment configuration

### CodeIgniter 4 Commands

```bash
# List all available commands
php spark list

# Clear cache
php spark cache:clear

# Create new controller
php spark make:controller ControllerName

# Create new model
php spark make:model ModelName
```

## 🧪 Testing

```bash
# Run all tests
php spark test

# Run specific test
php spark test tests/unit/HealthTest.php
```

## 📁 Project Structure

```
ClearPay/
├── app/
│   ├── Config/          # Configuration files
│   ├── Controllers/     # Application controllers
│   ├── Database/        # Migrations and seeds
│   ├── Models/          # Data models
│   ├── Views/           # View templates
│   └── ...
├── public/              # Web accessible files
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── uploads/        # Upload directories
│   └── index.php       # Entry point
├── vendor/             # Composer dependencies
├── writable/           # Writable directories
└── ...
```

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- CSRF protection
- XSS protection
- SQL injection prevention
- File upload validation
- Session management

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify MySQL is running in XAMPP
   - Check database credentials in `app/Config/Database.php`
   - Ensure database `clearpaydb` exists

2. **Permission Denied Errors**
   - Check file permissions on `writable/` directory
   - Ensure upload directories are writable

3. **Composer Issues**
   - Update Composer: `composer self-update`
   - Clear Composer cache: `composer clear-cache`
   - Reinstall dependencies: `composer install --no-cache`

4. **Migration Errors**
   - Check database connection
   - Verify all required tables exist
   - Run migrations in order

### Getting Help

If you encounter issues:

1. Check the [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
2. Review the application logs in `writable/logs/`
3. Check XAMPP error logs
4. Verify all prerequisites are installed correctly

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For support and questions:
- Create an issue in the GitHub repository
- Check the documentation
- Review the troubleshooting section

---

**Happy Coding! 🎉**
