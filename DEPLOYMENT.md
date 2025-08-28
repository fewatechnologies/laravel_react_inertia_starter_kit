# 🚀 Production Deployment Guide

## 🎯 Overview
This guide covers deploying the Laravel Multi-Dashboard Starter Kit to production environments.

## 🔧 Server Requirements

### Minimum System Requirements
- **OS**: Ubuntu 20.04+ / CentOS 8+ / Amazon Linux 2
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx (recommended) or Apache
- **Memory**: 2GB RAM minimum, 4GB+ recommended
- **Storage**: 10GB+ SSD storage
- **Network**: SSL certificate for HTTPS

### PHP Extensions Required
```bash
sudo apt-get install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl \
    php8.2-mbstring php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl \
    php8.2-redis php8.2-imagick
```

## 🌐 Web Server Configuration

### Nginx Configuration
Create `/etc/nginx/sites-available/laravel-dashboard`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/laravel-dashboard/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    index index.php;

    charset utf-8;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security: Block access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    client_max_body_size 100M;
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/laravel-dashboard /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🗄️ Database Setup

### MySQL Production Configuration
Create databases:
```sql
CREATE DATABASE laravel_dashboard_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE laravel_dashboard_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON laravel_dashboard_prod.* TO 'laravel_user'@'localhost';
GRANT ALL PRIVILEGES ON laravel_dashboard_test.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
```

### MySQL Optimization
Add to `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
max_connections = 200
query_cache_type = 1
query_cache_size = 64M
```

## 🚀 Application Deployment

### 1. Clone and Setup
```bash
cd /var/www
sudo git clone https://github.com/your-repo/laravel-dashboard.git
sudo chown -R www-data:www-data laravel-dashboard
cd laravel-dashboard
```

### 2. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev --no-interaction
npm ci --only=production
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` for production:
```env
APP_NAME="Your Dashboard App"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_dashboard_prod
DB_USERNAME=laravel_user
DB_PASSWORD=secure_password_here

# Test Database
DB_CONNECTION_TEST=test_mysql
DB_HOST_TEST=127.0.0.1
DB_PORT_TEST=3306
DB_DATABASE_TEST=laravel_dashboard_test
DB_USERNAME_TEST=laravel_user
DB_PASSWORD_TEST=secure_password_here

# Cache Configuration
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration (Production SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# SMS Configuration (Aakash SMS)
AAKASH_SMS_TOKEN=your-production-token
AAKASH_SMS_FROM=YourBrand
AAKASH_SMS_TEST_PHONE=98XXXXXXXX
```

### 4. Build and Cache
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 5. Database Migration
```bash
php artisan migrate --force
php artisan migrate --database=test_mysql --force
php artisan db:seed --class=ProductionSeeder --force
```

### 6. File Permissions
```bash
sudo chown -R www-data:www-data /var/www/laravel-dashboard
sudo chmod -R 755 /var/www/laravel-dashboard
sudo chmod -R 775 /var/www/laravel-dashboard/storage
sudo chmod -R 775 /var/www/laravel-dashboard/bootstrap/cache
```

## 🔒 Security Configuration

### 1. Firewall Setup (UFW)
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. Fail2Ban Configuration
Create `/etc/fail2ban/jail.local`:
```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 10

[nginx-botsearch]
enabled = true
port = http,https
logpath = /var/log/nginx/access.log
maxretry = 2
```

### 3. SSL Certificate (Let's Encrypt)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
sudo systemctl enable certbot.timer
```

## 📊 Monitoring & Logging

### 1. Laravel Telescope (Optional)
For production monitoring:
```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

### 2. Log Rotation
Create `/etc/logrotate.d/laravel`:
```
/var/www/laravel-dashboard/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        /usr/bin/systemctl reload php8.2-fpm > /dev/null 2>&1 || true
    endscript
}
```

### 3. System Monitoring
Install monitoring tools:
```bash
sudo apt install htop iotop nethogs
```

## 🔄 Backup Strategy

### 1. Database Backup Script
Create `/usr/local/bin/backup-laravel-db.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/laravel"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup main database
mysqldump -u laravel_user -p'secure_password_here' laravel_dashboard_prod > $BACKUP_DIR/main_db_$DATE.sql

# Backup test database
mysqldump -u laravel_user -p'secure_password_here' laravel_dashboard_test > $BACKUP_DIR/test_db_$DATE.sql

# Compress backups
gzip $BACKUP_DIR/main_db_$DATE.sql
gzip $BACKUP_DIR/test_db_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

### 2. Cron Jobs
Add to crontab:
```bash
# Database backups
0 2 * * * /usr/local/bin/backup-laravel-db.sh

# Laravel scheduler
* * * * * cd /var/www/laravel-dashboard && php artisan schedule:run >> /dev/null 2>&1

# Queue worker (if using queues)
* * * * * cd /var/www/laravel-dashboard && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

## 🚀 Deployment Automation

### 1. Deployment Script
Create `deploy.sh`:
```bash
#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Pull latest changes
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev --no-interaction
npm ci --only=production

# Build assets
npm run build

# Clear and cache
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "✅ Deployment completed successfully!"
```

### 2. Zero-Downtime Deployment
For high-availability deployments, consider using:
- **Laravel Envoy** for deployment automation
- **Blue-Green Deployment** strategy
- **Load balancers** for multiple servers

## 🔍 Health Checks

### Application Health Check
Create a health check endpoint in `routes/web.php`:
```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => config('app.version'),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
    ]);
});
```

### Monitoring Script
Create `/usr/local/bin/health-check.sh`:
```bash
#!/bin/bash
HEALTH_URL="https://your-domain.com/health"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" $HEALTH_URL)

if [ $RESPONSE -eq 200 ]; then
    echo "✅ Application is healthy"
else
    echo "❌ Application health check failed (HTTP $RESPONSE)"
    # Send alert (email, Slack, etc.)
fi
```

## 🚨 Troubleshooting

### Common Issues

1. **Permission Errors**
```bash
sudo chown -R www-data:www-data /var/www/laravel-dashboard
sudo chmod -R 775 storage bootstrap/cache
```

2. **Database Connection Issues**
- Check MySQL service: `sudo systemctl status mysql`
- Verify credentials in `.env`
- Test connection: `php artisan tinker` then `DB::connection()->getPdo()`

3. **Asset Loading Issues**
- Rebuild assets: `npm run build`
- Clear cache: `php artisan cache:clear`
- Check file permissions

4. **Queue Worker Issues**
```bash
# Restart queue workers
php artisan queue:restart

# Monitor queue
php artisan queue:monitor
```

### Performance Optimization

1. **Enable OPcache**
Add to `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

2. **Redis Configuration**
Optimize Redis for caching:
```bash
# /etc/redis/redis.conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

---

## 📞 Support

For deployment issues:
1. Check application logs: `/var/www/laravel-dashboard/storage/logs/`
2. Check web server logs: `/var/log/nginx/`
3. Monitor system resources: `htop`, `df -h`
4. Test database connections: `php artisan tinker`

**Remember**: Always test deployments in a staging environment first!