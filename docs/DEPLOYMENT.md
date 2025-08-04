# Panduan Deployment Sambatan Coffee & Space Website

## 📋 Daftar Isi
1. [Persyaratan Server](#persyaratan-server)
2. [Persiapan Server](#persiapan-server)
3. [Upload dan Konfigurasi](#upload-dan-konfigurasi)
4. [Konfigurasi Database](#konfigurasi-database)
5. [Konfigurasi SSL](#konfigurasi-ssl)
6. [Optimasi Performance](#optimasi-performance)
7. [Monitoring dan Backup](#monitoring-dan-backup)
8. [Troubleshooting](#troubleshooting)

## 🖥️ Persyaratan Server

### Minimum Requirements
- **Web Server**: Apache 2.4+ atau Nginx 1.18+
- **PHP**: 8.0+ dengan ekstensi:
  - PDO
  - GD atau ImageMagick
  - cURL
  - OpenSSL
  - JSON
  - mbstring
  - fileinfo
- **Database**: MySQL 8.0+ atau MariaDB 10.5+
- **Storage**: 2GB ruang disk minimum
- **RAM**: 1GB minimum, 2GB recommended
- **SSL Certificate**: Wajib untuk production

### Recommended Requirements
- **CPU**: 2 vCPU cores
- **RAM**: 4GB
- **Storage**: 10GB SSD
- **Bandwidth**: 100Mbps
- **CDN**: CloudFlare atau AWS CloudFront

## 🚀 Persiapan Server

### 1. Update Server
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 2. Install Apache dan PHP
```bash
# Ubuntu/Debian
sudo apt install apache2 php8.0 php8.0-mysql php8.0-gd php8.0-curl php8.0-mbstring php8.0-xml php8.0-zip libapache2-mod-php8.0 -y

# CentOS/RHEL
sudo yum install httpd php php-mysql php-gd php-curl php-mbstring php-xml php-zip -y
```

### 3. Install MySQL
```bash
# Ubuntu/Debian
sudo apt install mysql-server -y

# CentOS/RHEL
sudo yum install mysql-server -y
```

### 4. Konfigurasi MySQL
```bash
sudo mysql_secure_installation
```

### 5. Aktivasi Modul Apache
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl restart apache2
```

## 📁 Upload dan Konfigurasi

### 1. Upload Files
Upload semua file ke direktori web server (biasanya `/var/www/html` atau `/public_html`)

```bash
# Jika menggunakan FileZilla, WinSCP, atau SCP
scp -r /path/to/sambatan/* user@server:/var/www/html/
```

### 2. Set Permissions
```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/html/

# Set permissions untuk files
sudo find /var/www/html/ -type f -exec chmod 644 {} \;

# Set permissions untuk directories
sudo find /var/www/html/ -type d -exec chmod 755 {} \;

# Special permissions untuk upload directories
sudo chmod 755 /var/www/html/uploads/
sudo chmod 755 /var/www/html/assets/menu/
```

### 3. Konfigurasi Apache Virtual Host
Buat file konfigurasi virtual host:

```bash
sudo nano /etc/apache2/sites-available/sambatan.conf
```

Isi dengan:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/sambatan_error.log
    CustomLog ${APACHE_LOG_DIR}/sambatan_access.log combined
</VirtualHost>
```

Aktivasi site:
```bash
sudo a2ensite sambatan.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

## 🗄️ Konfigurasi Database

### 1. Buat Database dan User
```sql
mysql -u root -p

CREATE DATABASE sambatan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sambatan_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON sambatan_db.* TO 'sambatan_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Import Database Schema
```bash
mysql -u sambatan_user -p sambatan_db < database/sambatan_db.sql
```

### 3. Update Config File
Edit file `config/config.php`:
```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sambatan_db');
define('DB_USER', 'sambatan_user');
define('DB_PASS', 'strong_password_here');

// Update site URL
define('SITE_URL', 'https://yourdomain.com');

// Set production mode
define('DEBUG', false);
define('ENVIRONMENT', 'production');
```

## 🔒 Konfigurasi SSL

### 1. Install Certbot (Let's Encrypt)
```bash
# Ubuntu/Debian
sudo apt install certbot python3-certbot-apache -y

# CentOS/RHEL
sudo yum install certbot python3-certbot-apache -y
```

### 2. Generate SSL Certificate
```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

### 3. Auto-renewal
```bash
sudo crontab -e
```
Tambahkan:
```bash
0 12 * * * /usr/bin/certbot renew --quiet
```

### 4. Update Virtual Host untuk HTTPS
Certbot akan otomatis membuat konfigurasi HTTPS, tapi pastikan ada:
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html
    
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
    
    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    # Include existing directory and security configurations
</VirtualHost>
```

## ⚡ Optimasi Performance

### 1. Konfigurasi PHP
Edit `/etc/php/8.0/apache2/php.ini`:
```ini
# Memory dan execution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

# File uploads
upload_max_filesize = 10M
post_max_size = 10M

# Opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1

# Session
session.gc_maxlifetime = 3600
session.cache_expire = 180
```

### 2. Konfigurasi MySQL
Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
# Performance optimizations
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query cache (jika MySQL < 8.0)
query_cache_type = 1
query_cache_size = 128M

# Connection settings
max_connections = 100
wait_timeout = 300
```

### 3. Setup Caching
Install dan konfigurasi Redis (optional):
```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
```

### 4. Konfigurasi Gzip dan Caching
File `.htaccess` sudah dikonfigurasi, pastikan modul Apache aktif:
```bash
sudo a2enmod deflate
sudo a2enmod expires
sudo a2enmod headers
sudo systemctl restart apache2
```

## 📊 Monitoring dan Backup

### 1. Setup Log Rotation
```bash
sudo nano /etc/logrotate.d/sambatan
```

Isi dengan:
```
/var/log/apache2/sambatan_*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 640 www-data adm
    postrotate
        systemctl reload apache2
    endscript
}
```

### 2. Database Backup Script
Buat script backup otomatis:
```bash
sudo nano /usr/local/bin/backup_sambatan.sh
```

Isi dengan:
```bash
#!/bin/bash

# Configuration
DB_NAME="sambatan_db"
DB_USER="sambatan_user"
DB_PASS="strong_password_here"
BACKUP_DIR="/var/backups/sambatan"
DATE=$(date +"%Y%m%d_%H%M%S")

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /var/www/html --exclude='/var/www/html/uploads/temp'

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

Set permissions dan jalankan:
```bash
sudo chmod +x /usr/local/bin/backup_sambatan.sh

# Test backup
sudo /usr/local/bin/backup_sambatan.sh

# Schedule daily backup
sudo crontab -e
```
Tambahkan:
```
0 2 * * * /usr/local/bin/backup_sambatan.sh
```

### 3. Monitoring Script
Buat script monitoring:
```bash
sudo nano /usr/local/bin/monitor_sambatan.sh
```

Isi dengan:
```bash
#!/bin/bash

# Check website status
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com)
if [ $HTTP_STATUS -ne 200 ]; then
    echo "Website down! HTTP Status: $HTTP_STATUS" | mail -s "Sambatan Website Alert" admin@yourdomain.com
fi

# Check database connection
mysql -u sambatan_user -pstrong_password_here -e "SELECT 1" sambatan_db > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "Database connection failed!" | mail -s "Sambatan Database Alert" admin@yourdomain.com
fi

# Check disk space
DISK_USAGE=$(df /var/www/html | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 85 ]; then
    echo "Disk usage high: $DISK_USAGE%" | mail -s "Sambatan Disk Alert" admin@yourdomain.com
fi
```

Set permissions dan schedule:
```bash
sudo chmod +x /usr/local/bin/monitor_sambatan.sh

# Check every 5 minutes
sudo crontab -e
```
Tambahkan:
```
*/5 * * * * /usr/local/bin/monitor_sambatan.sh
```

## 🔧 Troubleshooting

### 1. Website Tidak Bisa Diakses
```bash
# Check Apache status
sudo systemctl status apache2

# Check Apache error logs
sudo tail -f /var/log/apache2/sambatan_error.log

# Check Apache configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### 2. Database Connection Error
```bash
# Check MySQL status
sudo systemctl status mysql

# Check MySQL logs
sudo tail -f /var/log/mysql/error.log

# Test database connection
mysql -u sambatan_user -p sambatan_db
```

### 3. Permission Issues
```bash
# Reset permissions
sudo chown -R www-data:www-data /var/www/html/
sudo find /var/www/html/ -type f -exec chmod 644 {} \;
sudo find /var/www/html/ -type d -exec chmod 755 {} \;
```

### 4. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run

# Check SSL configuration
openssl s_client -connect yourdomain.com:443
```

### 5. Performance Issues
```bash
# Check server resources
htop
df -h
free -h

# Check Apache processes
ps aux | grep apache2

# Check MySQL processes
mysqladmin processlist -u root -p
```

### 6. Debug Mode
Untuk debugging, aktifkan mode debug sementara di `config/config.php`:
```php
define('DEBUG', true);
define('ENVIRONMENT', 'development');
```

**Jangan lupa matikan setelah selesai debugging!**

## 📝 Checklist Deployment

- [ ] Server memenuhi persyaratan minimum
- [ ] Apache/Nginx terkonfigurasi dengan benar
- [ ] PHP dan ekstensi terinstall
- [ ] MySQL/MariaDB terkonfigurasi
- [ ] Files diupload dengan permissions yang benar
- [ ] Database dibuat dan diimport
- [ ] Config file diupdate
- [ ] SSL certificate terinstall
- [ ] .htaccess berfungsi (test rewrite rules)
- [ ] Upload directory writable
- [ ] Admin panel bisa diakses
- [ ] Order system berfungsi
- [ ] Email notifications bekerja
- [ ] Backup system aktif
- [ ] Monitoring aktif
- [ ] Website ditest dari berbagai device

## 🆘 Support

Jika mengalami kesulitan dalam deployment:

1. Check log files di `/var/log/apache2/` dan `/var/log/mysql/`
2. Pastikan semua service running: `sudo systemctl status apache2 mysql`
3. Test configuration: `sudo apache2ctl configtest`
4. Check firewall settings: `sudo ufw status`

Untuk bantuan lebih lanjut, hubungi:
- Email: support@sambatan.com
- WhatsApp: +62 812-3456-7890

---

**Catatan**: Panduan ini dibuat untuk server Ubuntu/Debian. Untuk distribusi lain, sesuaikan command yang diperlukan.
