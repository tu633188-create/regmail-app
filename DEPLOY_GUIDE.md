# 🚀 Deploy Guide - Laravel Application

## Prerequisites
- PHP >= 8.1
- Composer
- Database (MySQL/PostgreSQL/SQLite)
- Web server (Apache/Nginx)

## Deploy Commands

### 1. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 2. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit .env file with your configuration:
# - Database credentials
# - Mail settings
# - JWT secret
# - Telegram bot token (if using Telegram features)
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Optional: Seed database with admin user
php artisan db:seed
```

### 5. Cache Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Proper Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Restart Web Server
```bash
# For Apache:
sudo systemctl restart apache2

# For Nginx:
sudo systemctl restart nginx
```

### 8. Optional: Queue Worker
```bash
# If using queues (for background jobs):
php artisan queue:work --daemon
```

## Environment Variables (.env)

### Required Settings:
```env
APP_NAME="RegMail"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=regmail
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=your-jwt-secret-key
JWT_ALGO=HS256

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Telegram Bot (if using Telegram features)
TELEGRAM_BOT_TOKEN=your-bot-token
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
```

## Features Included

### ✅ Core Features:
- User registration and authentication
- JWT token management
- Email submission system
- Device tracking
- Activity logging
- API usage tracking

### ✅ Admin Panel (Filament):
- User management
- Registration management
- Device management
- Telegram settings management
- System settings
- Import/Export functionality

### ✅ API Endpoints:
- User registration
- Email submission
- Device management
- Telegram integration
- Admin operations

## Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Configure proper file permissions
- [ ] Set up SSL/HTTPS
- [ ] Configure firewall rules
- [ ] Regular security updates

## Troubleshooting

### Common Issues:

1. **Permission Denied:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   chmod -R 755 storage bootstrap/cache
   ```

2. **Database Connection Error:**
   - Check database credentials in `.env`
   - Ensure database server is running
   - Verify database exists

3. **Cache Issues:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Queue Not Working:**
   ```bash
   php artisan queue:restart
   ```

## Monitoring

### Log Files:
- Application logs: `storage/logs/laravel.log`
- Web server logs: `/var/log/apache2/` or `/var/log/nginx/`

### Health Check:
```bash
# Check if application is running
curl -I https://yourdomain.com

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Backup

### Database Backup:
```bash
mysqldump -u username -p database_name > backup.sql
```

### Application Backup:
```bash
tar -czf regmail-backup-$(date +%Y%m%d).tar.gz /path/to/application
```

## Updates

### To update the application:
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

**Created:** $(date)
**Repository:** https://github.com/tu633188-create/regmail-app
