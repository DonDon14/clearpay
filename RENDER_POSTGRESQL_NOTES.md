# 📊 PostgreSQL Migration Notes for Render.com

Render.com's **free tier only supports PostgreSQL**, not MySQL. This guide helps you understand what's needed.

---

## ✅ What's Been Updated

### Configuration Files

1. **`render.yaml`**
   - ✅ Updated to use PostgreSQL (free tier)
   - ✅ Database type automatically set to PostgreSQL

2. **`Dockerfile`**
   - ✅ Added PostgreSQL PHP extensions (`pdo_pgsql`, `pgsql`)
   - ✅ Installed `libpq-dev` for PostgreSQL support

3. **`app/Config/Database.php`**
   - ✅ Auto-detects PostgreSQL from `DATABASE_URL`
   - ✅ Sets correct port (5432) and schema (public)
   - ✅ Configures PostgreSQL-specific settings

---

## 🔄 Database Migration Considerations

### CodeIgniter 4 Compatibility

CodeIgniter 4 supports both MySQL and PostgreSQL, but there are some differences:

1. **Data Types:**
   - MySQL: `AUTO_INCREMENT` → PostgreSQL: `SERIAL` or `BIGSERIAL`
   - MySQL: `DATETIME` → PostgreSQL: `TIMESTAMP`
   - MySQL: `TEXT` → PostgreSQL: `TEXT` (same)

2. **SQL Syntax:**
   - MySQL: `` `backticks` `` for identifiers
   - PostgreSQL: `"double quotes"` for identifiers
   - CodeIgniter handles this automatically

3. **Functions:**
   - Some MySQL-specific functions may need adjustment
   - CodeIgniter's Query Builder handles most differences

### Migration Steps

1. **Run Migrations:**
   ```bash
   php spark migrate
   ```
   CodeIgniter will automatically adapt migrations for PostgreSQL.

2. **Check for Issues:**
   - Review migration logs
   - Test all database operations
   - Verify data integrity

3. **Common Issues:**
   - **AUTO_INCREMENT:** CodeIgniter handles this automatically
   - **Date/Time:** Usually compatible
   - **String Functions:** May need adjustment if using raw SQL

---

## 🔧 Environment Variables

When Render creates the PostgreSQL database, it automatically provides:

- **`DATABASE_URL`** - Full connection string (auto-detected by app)
- Individual variables can also be set:
  ```
  DB_HOST = (from database info)
  DB_PORT = 5432
  DB_NAME = clearpaydb
  DB_USER = (from database info)
  DB_PASSWORD = (from database password)
  DB_DRIVER = Postgre
  ```

---

## 📝 Important Notes

### Render Free Tier PostgreSQL Limits

- **Database Size:** 1 GB (free tier)
- **Connections:** Limited (usually sufficient for small apps)
- **Backups:** Automatic (retention varies by plan)

### Migration from MySQL to PostgreSQL

If you're migrating existing data:

1. **Export MySQL Data:**
   ```bash
   mysqldump -u user -p database > backup.sql
   ```

2. **Convert SQL (if needed):**
   - Some SQL syntax may need manual adjustment
   - Use migration tools if available

3. **Import to PostgreSQL:**
   - Use `psql` or pgAdmin
   - Or use CodeIgniter migrations

### Testing

After deployment:

1. **Test Database Connection:**
   - Visit your app
   - Check for database errors

2. **Test CRUD Operations:**
   - Create records
   - Read records
   - Update records
   - Delete records

3. **Test Migrations:**
   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

---

## 🐛 Troubleshooting

### Issue: "Driver not found"

**Solution:**
- Verify PostgreSQL extensions in Dockerfile
- Rebuild Docker image

### Issue: "Connection refused"

**Solution:**
- Check database is running
- Verify `DATABASE_URL` is correct
- Check database region matches web service

### Issue: "Table doesn't exist"

**Solution:**
- Run migrations: `php spark migrate`
- Check migration logs
- Verify schema name (usually `public`)

### Issue: "Syntax error in SQL"

**Solution:**
- Check for MySQL-specific syntax
- Use CodeIgniter Query Builder instead of raw SQL
- Review migration files

---

## 📚 Resources

- **CodeIgniter PostgreSQL Guide:** https://codeigniter.com/user_guide/database/postgre.html
- **PostgreSQL Documentation:** https://www.postgresql.org/docs/
- **Render Database Docs:** https://render.com/docs/databases

---

## ✅ Checklist

Before deploying:

- [ ] `render.yaml` uses PostgreSQL (free tier)
- [ ] `Dockerfile` includes PostgreSQL extensions
- [ ] `Database.php` configured for PostgreSQL
- [ ] Migrations tested locally (if possible)
- [ ] Environment variables ready

After deployment:

- [ ] Database connection works
- [ ] Migrations run successfully
- [ ] Seeders run successfully
- [ ] All CRUD operations work
- [ ] No PostgreSQL-specific errors

---

**Last Updated:** 2024

