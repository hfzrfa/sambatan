#!/bin/bash

# Sambatan Website Setup Script
# This script automates the setup process for Sambatan Coffee & Space website

echo "=========================================="
echo "    Sambatan Website Setup Script"
echo "=========================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. Some operations may require different permissions."
fi

# 1. Check system requirements
print_status "Checking system requirements..."

# Check PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.0 or higher."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_success "PHP version: $PHP_VERSION"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    print_error "MySQL is not installed. Please install MySQL 8.0 or higher."
    exit 1
fi

MYSQL_VERSION=$(mysql --version)
print_success "MySQL found: $MYSQL_VERSION"

# Check Apache/Nginx
if command -v apache2 &> /dev/null; then
    print_success "Apache2 found"
    WEB_SERVER="apache2"
elif command -v nginx &> /dev/null; then
    print_success "Nginx found"
    WEB_SERVER="nginx"
else
    print_warning "No web server detected. Please install Apache or Nginx."
fi

# 2. Setup directory structure
print_status "Setting up directory structure..."

# Create uploads directories
mkdir -p uploads/menu
mkdir -p uploads/gallery
mkdir -p uploads/categories
mkdir -p logs
mkdir -p backup

# Set permissions
chmod 755 uploads/
chmod 755 uploads/menu/
chmod 755 uploads/gallery/
chmod 755 uploads/categories/
chmod 755 logs/
chmod 755 backup/

print_success "Directory structure created"

# 3. Database setup
print_status "Setting up database..."

read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASSWORD
echo ""

# Test MySQL connection
if ! mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1;" &> /dev/null; then
    print_error "Cannot connect to MySQL. Please check your password."
    exit 1
fi

# Create database
print_status "Creating database..."
mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS sambatan_db;"

# Import schema
if [ -f "database/sambatan_db.sql" ]; then
    print_status "Importing database schema..."
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" sambatan_db < database/sambatan_db.sql
    print_success "Database schema imported"
else
    print_error "Database schema file not found!"
    exit 1
fi

# Create database user
read -p "Create dedicated database user? (y/n): " CREATE_USER
if [ "$CREATE_USER" = "y" ] || [ "$CREATE_USER" = "Y" ]; then
    read -p "Enter database username: " DB_USER
    read -p "Enter database password: " -s DB_PASSWORD
    echo ""
    
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON sambatan_db.* TO '$DB_USER'@'localhost';"
    mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"
    
    print_success "Database user created"
else
    DB_USER="root"
    DB_PASSWORD="$MYSQL_ROOT_PASSWORD"
fi

# 4. Configuration setup
print_status "Setting up configuration..."

# Get site URL
read -p "Enter your site URL (e.g., https://sambatan.com): " SITE_URL

# Create config file
cat > config/config.php << EOF
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASSWORD');
define('DB_NAME', 'sambatan_db');

// Site configuration
define('SITE_URL', '$SITE_URL');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Create connection
try {
    \$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die("Connection failed: " . \$e->getMessage());
}

// Helper functions
function sanitize(\$input) {
    return htmlspecialchars(strip_tags(trim(\$input)));
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

function formatPrice(\$price) {
    return 'Rp ' . number_format(\$price, 0, ',', '.');
}

function uploadImage(\$file, \$folder = 'menu') {
    if (!isset(\$file['error']) || \$file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    \$allowed = ['jpg', 'jpeg', 'png', 'gif'];
    \$filename = \$file['name'];
    \$extension = strtolower(pathinfo(\$filename, PATHINFO_EXTENSION));
    
    if (!in_array(\$extension, \$allowed)) {
        return false;
    }
    
    \$newname = uniqid() . '.' . \$extension;
    \$destination = UPLOAD_PATH . \$folder . '/' . \$newname;
    
    if (move_uploaded_file(\$file['tmp_name'], \$destination)) {
        return \$folder . '/' . \$newname;
    }
    
    return false;
}

// Start session
session_start();
?>
EOF

print_success "Configuration file created"

# 5. Web server configuration
print_status "Setting up web server configuration..."

if [ "$WEB_SERVER" = "apache2" ]; then
    # Create Apache virtual host
    read -p "Create Apache virtual host? (y/n): " CREATE_VHOST
    if [ "$CREATE_VHOST" = "y" ] || [ "$CREATE_VHOST" = "Y" ]; then
        read -p "Enter domain name (e.g., sambatan.local): " DOMAIN_NAME
        
        cat > /tmp/sambatan.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    DocumentRoot $(pwd)
    
    <Directory $(pwd)>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/sambatan_error.log
    CustomLog \${APACHE_LOG_DIR}/sambatan_access.log combined
</VirtualHost>
EOF
        
        print_warning "Apache virtual host configuration created at /tmp/sambatan.conf"
        print_warning "Please move it to /etc/apache2/sites-available/ and enable it manually:"
        print_warning "sudo cp /tmp/sambatan.conf /etc/apache2/sites-available/"
        print_warning "sudo a2ensite sambatan.conf"
        print_warning "sudo systemctl reload apache2"
    fi
    
    # Create .htaccess
    cat > .htaccess << EOF
# Sambatan Website .htaccess

# Enable rewrite engine
RewriteEngine On

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Enable compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Browser caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>

# Protect admin directory
<Files "admin/login.php">
    # Allow access to login page
</Files>

# Hide sensitive files
<FilesMatch "\\.(env|log|ini)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent access to config directory
RedirectMatch 404 /config/.*

# Custom error pages
ErrorDocument 404 /404.html
ErrorDocument 500 /500.html
EOF
    
    print_success ".htaccess file created"
fi

# 6. Install Composer dependencies (if composer.json exists)
if [ -f "composer.json" ]; then
    print_status "Installing Composer dependencies..."
    
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader
        print_success "Composer dependencies installed"
    else
        print_warning "Composer not found. Please install dependencies manually."
    fi
fi

# 7. Set up cron jobs
print_status "Setting up cron jobs..."

read -p "Set up automated backups? (y/n): " SETUP_BACKUP
if [ "$SETUP_BACKUP" = "y" ] || [ "$SETUP_BACKUP" = "Y" ]; then
    # Create backup script
    cat > backup/backup.sh << 'EOF'
#!/bin/bash

# Sambatan Website Backup Script

BACKUP_DIR="/path/to/backup/directory"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DB_NAME="sambatan_db"
DB_USER="your_db_user"
DB_PASSWORD="your_db_password"

# Create backup directory
mkdir -p "$BACKUP_DIR/$TIMESTAMP"

# Database backup
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$BACKUP_DIR/$TIMESTAMP/database.sql"

# Files backup
tar -czf "$BACKUP_DIR/$TIMESTAMP/files.tar.gz" --exclude='backup' --exclude='logs' .

# Clean old backups (keep last 7 days)
find "$BACKUP_DIR" -type d -mtime +7 -exec rm -rf {} +

echo "Backup completed: $TIMESTAMP"
EOF
    
    chmod +x backup/backup.sh
    
    print_success "Backup script created at backup/backup.sh"
    print_warning "Please update the database credentials in the backup script"
    print_warning "Add to crontab for automated backups:"
    print_warning "0 2 * * * /path/to/sambatan/backup/backup.sh"
fi

# 8. Create systemd service (optional)
read -p "Create systemd service for background tasks? (y/n): " CREATE_SERVICE
if [ "$CREATE_SERVICE" = "y" ] || [ "$CREATE_SERVICE" = "Y" ]; then
    cat > /tmp/sambatan.service << EOF
[Unit]
Description=Sambatan Background Tasks
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=$(pwd)
ExecStart=/usr/bin/php -f $(pwd)/scripts/background-tasks.php
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
    
    print_warning "Systemd service file created at /tmp/sambatan.service"
    print_warning "To install: sudo cp /tmp/sambatan.service /etc/systemd/system/"
    print_warning "Then: sudo systemctl enable sambatan && sudo systemctl start sambatan"
fi

# 9. Security recommendations
print_status "Security recommendations..."

print_warning "Security checklist:"
echo "  1. Change default admin password"
echo "  2. Set up SSL certificate"
echo "  3. Configure firewall"
echo "  4. Regular security updates"
echo "  5. Monitor log files"
echo "  6. Backup database regularly"

# 10. Final checks
print_status "Running final checks..."

# Check PHP extensions
REQUIRED_EXTENSIONS=("mysqli" "pdo" "pdo_mysql" "gd" "json" "session" "curl")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        print_success "PHP extension $ext: OK"
    else
        print_error "PHP extension $ext: MISSING"
    fi
done

# Check file permissions
if [ -w "uploads/" ]; then
    print_success "Uploads directory: Writable"
else
    print_error "Uploads directory: Not writable"
fi

# Test database connection
if php -r "
require_once 'config/config.php';
try {
    \$pdo->query('SELECT 1');
    echo 'Database connection: OK' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"; then
    print_success "Database connection test passed"
else
    print_error "Database connection test failed"
fi

# 11. Setup complete
print_success "Setup completed successfully!"
echo ""
echo "=========================================="
echo "           Setup Summary"
echo "=========================================="
echo "Website URL: $SITE_URL"
echo "Admin URL: $SITE_URL/admin"
echo "Database: sambatan_db"
echo "Default admin credentials:"
echo "  Username: admin"
echo "  Password: password"
echo ""
echo "Next steps:"
echo "1. Access $SITE_URL/admin/login.php"
echo "2. Change default password"
echo "3. Configure payment gateway settings"
echo "4. Add menu items and categories"
echo "5. Test ordering system"
echo ""
echo "Documentation: docs/README.md"
echo "Payment guide: docs/payment-gateway-guide.md"
echo ""
print_warning "Remember to:"
echo "  - Set up SSL certificate for production"
echo "  - Configure payment gateway"
echo "  - Set up monitoring and backups"
echo "  - Test all functionality"
echo ""
print_success "Sambatan Website is ready to use!"
