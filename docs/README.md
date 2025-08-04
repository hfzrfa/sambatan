# Sambatan Coffee & Space - Dokumentasi Website

## 📋 Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Instalasi](#instalasi)
3. [Konfigurasi](#konfigurasi)
4. [Fitur Website](#fitur-website)
5. [Panel Admin](#panel-admin)
6. [Database](#database)
7. [API Documentation](#api-documentation)
8. [Payment Gateway](#payment-gateway)
9. [Maintenance](#maintenance)
10. [Troubleshooting](#troubleshooting)

---

## 📖 Pendahuluan

Sambatan Coffee & Space adalah website modern untuk bisnis kafe yang dilengkapi dengan:
- Website responsive dan PWA (Progressive Web App)
- Sistem pemesanan online
- Panel admin untuk manajemen
- Integrasi payment gateway
- Sistem notifikasi WhatsApp

### Teknologi yang Digunakan
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Backend**: PHP 8.0+, MySQL 8.0+
- **Framework CSS**: Bootstrap 5.3.0
- **Icons**: Font Awesome 6.0
- **Animations**: AOS (Animate On Scroll)
- **PWA**: Service Worker, Web App Manifest

---

## 🚀 Instalasi

### Persyaratan Sistem
- PHP 8.0 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Apache/Nginx Web Server
- Composer (untuk dependencies)
- SSL Certificate (untuk production)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/your-repo/sambatan-website.git
cd sambatan-website
```

2. **Setup Database**
```bash
# Import database schema
mysql -u root -p < database/sambatan_db.sql
```

3. **Konfigurasi File**
```php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'sambatan_db');
define('SITE_URL', 'https://yourdomain.com');
```

4. **Set Permissions**
```bash
chmod 755 uploads/
chmod 755 uploads/menu/
chmod 755 uploads/gallery/
chmod 755 uploads/categories/
```

5. **Install Dependencies** (jika menggunakan Composer)
```bash
composer install
```

---

## ⚙️ Konfigurasi

### Konfigurasi Database
File: `config/config.php`

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sambatan_db');

// Site configuration
define('SITE_URL', 'http://localhost/sambatan');
define('ADMIN_URL', SITE_URL . '/admin');

// Upload configuration
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
?>
```

### Konfigurasi PWA
File: `manifest.json`

```json
{
  "name": "Sambatan Coffee & Space",
  "short_name": "Sambatan",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#1C403E",
  "theme_color": "#F0B33C"
}
```

### Konfigurasi Service Worker
File: `sw.js` - Otomatis cache resources untuk offline access

---

## 🌟 Fitur Website

### 1. Homepage
- **Hero Section**: Banner utama dengan call-to-action
- **About Section**: Informasi tentang kafe
- **Menu Preview**: Tampilan menu unggulan
- **Reviews**: Ulasan pelanggan
- **Contact**: Informasi kontak dan lokasi

### 2. Menu Page (`menu.php`)
- Filter berdasarkan kategori
- Pencarian menu
- Detail harga dan deskripsi
- Responsive grid layout
- Lazy loading images

### 3. Order System (`order.php`)
- Keranjang belanja dinamis
- Pilihan jenis pesanan (dine-in, takeaway, delivery)
- Kalkulasi total otomatis
- WhatsApp integration

### 4. Checkout (`checkout.php`)
- Form informasi pelanggan
- Validasi data
- Summary pesanan
- Redirect ke payment gateway

### 5. PWA Features
- **Offline Support**: Cache essential resources
- **Install Prompt**: Prompt untuk install app
- **Push Notifications**: Notifikasi pesanan
- **Responsive Design**: Mobile-first approach

---

## 👨‍💼 Panel Admin

### Login Admin
URL: `/admin/login.php`
- Username: `admin`
- Password: `password` (ganti setelah login pertama)

### Dashboard (`/admin/dashboard.php`)
- Statistik pesanan, pelanggan, pendapatan
- Grafik penjualan
- Pesanan terbaru
- Menu populer

### Manajemen Menu (`/admin/menu.php`)
**Fitur CRUD:**
- ✅ Create: Tambah menu baru
- ✅ Read: Lihat daftar menu
- ✅ Update: Edit menu existing
- ✅ Delete: Hapus menu

**Fields:**
- Nama menu
- Kategori
- Deskripsi
- Harga
- Gambar
- Status (available/unavailable)
- Featured (ya/tidak)

### Manajemen Kategori (`/admin/categories.php`)
- CRUD kategori menu
- Upload gambar kategori
- Status aktif/nonaktif

### Manajemen Pesanan (`/admin/orders.php`)
- Lihat semua pesanan
- Update status pesanan
- Print invoice
- Export data

### Manajemen Pelanggan (`/admin/customers.php`)
- Database pelanggan
- Riwayat pesanan
- Export data pelanggan

### Ulasan (`/admin/reviews.php`)
- Moderasi ulasan
- Approve/reject ulasan
- Rating summary

### Galeri (`/admin/gallery.php`)
- Upload foto kafe
- Organize galeri
- Alt text untuk SEO

### Pengaturan (`/admin/settings.php`)
- Informasi kafe
- Jam operasional
- Biaya delivery
- Payment gateway settings
- WhatsApp number

### Laporan (`/admin/reports.php`)
- Laporan penjualan
- Laporan pelanggan
- Export ke Excel/PDF
- Filter by date range

---

## 🗄️ Database

### Schema Database

#### Tabel: `admin`
```sql
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Tabel: `categories`
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Tabel: `menu_items`
```sql
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

#### Tabel: `customers`
```sql
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Tabel: `orders`
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash', 'transfer', 'e_wallet', 'credit_card') DEFAULT 'cash',
    order_type ENUM('dine_in', 'takeaway', 'delivery') DEFAULT 'dine_in',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
```

#### Tabel: `order_items`
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);
```

### Backup Database
```bash
# Backup
mysqldump -u username -p sambatan_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore
mysql -u username -p sambatan_db < backup_file.sql
```

---

## 🔗 API Documentation

### Menu API

#### Get All Menu Items
```http
GET /api/menu.php
```

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Kopi Sambatan Special",
      "description": "Kopi house blend dengan rasa khas",
      "price": 25000,
      "category": "Kopi Panas",
      "image": "menu/kopi-special.jpg",
      "is_featured": true,
      "status": "available"
    }
  ]
}
```

#### Get Menu by Category
```http
GET /api/menu.php?category_id=1
```

### Order API

#### Create Order
```http
POST /api/orders.php
Content-Type: application/json

{
  "customer_name": "John Doe",
  "customer_phone": "081234567890",
  "customer_email": "john@example.com",
  "order_type": "dine_in",
  "items": [
    {
      "menu_item_id": 1,
      "quantity": 2,
      "price": 25000
    }
  ],
  "total_amount": 50000,
  "notes": "Ekstra gula"
}
```

#### Get Order Status
```http
GET /api/orders.php?order_number=ORD20241201001
```

---

## 💳 Payment Gateway

### Midtrans Integration

#### 1. Setup
```php
// payment/midtrans-config.php
\Midtrans\Config::$serverKey = 'SB-Mid-server-xxxxxxxx';
\Midtrans\Config::$isProduction = false; // Set true untuk production
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;
```

#### 2. Create Payment
```php
$transaction_details = array(
    'order_id' => $order_number,
    'gross_amount' => $total_amount,
);

$customer_details = array(
    'first_name' => $customer_name,
    'email' => $customer_email,
    'phone' => $customer_phone,
);

$transaction = array(
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
);

$snapToken = \Midtrans\Snap::getSnapToken($transaction);
```

#### 3. Frontend Integration
```javascript
snap.pay(snapToken, {
    onSuccess: function(result) {
        window.location.href = 'success.php?order_id=' + orderId;
    },
    onPending: function(result) {
        window.location.href = 'pending.php?order_id=' + orderId;
    },
    onError: function(result) {
        window.location.href = 'failed.php?order_id=' + orderId;
    }
});
```

### Payment Methods Supported
- 🏧 Bank Transfer (BCA, BNI, BRI, Mandiri)
- 💳 Credit/Debit Card (Visa, Mastercard)
- 💰 E-Wallet (GoPay, OVO, DANA, LinkAja)
- 🏪 Retail Outlets (Alfamart, Indomaret)

---

## 🔧 Maintenance

### Log Files
```bash
# Application logs
tail -f /var/log/sambatan/application.log

# Payment logs
tail -f /var/log/sambatan/payment.log

# Error logs
tail -f /var/log/apache2/error.log
```

### Database Maintenance
```sql
-- Optimize tables
OPTIMIZE TABLE menu_items, orders, order_items, customers;

-- Clean old sessions
DELETE FROM sessions WHERE last_activity < (UNIX_TIMESTAMP() - 86400);

-- Archive old orders (older than 1 year)
INSERT INTO orders_archive SELECT * FROM orders WHERE order_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
DELETE FROM orders WHERE order_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### File Cleanup
```bash
# Clean old log files
find /var/log/sambatan/ -name "*.log" -mtime +30 -delete

# Clean temporary files
find /tmp/ -name "sambatan_*" -mtime +7 -delete

# Optimize images
find uploads/ -name "*.jpg" -exec jpegoptim --max=85 {} \;
find uploads/ -name "*.png" -exec optipng -o2 {} \;
```

### Performance Monitoring
```bash
# Monitor MySQL processes
mysqladmin -u root -p processlist

# Check disk usage
df -h

# Monitor memory usage
free -m

# Check website response time
curl -w "%{time_total}\n" -o /dev/null -s https://yourdomain.com
```

---

## 🚨 Troubleshooting

### Common Issues

#### 1. Database Connection Error
**Problem**: `Connection failed: Access denied`

**Solution**:
```php
// Check config/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'correct_username');
define('DB_PASS', 'correct_password');
define('DB_NAME', 'sambatan_db');

// Test connection
mysql -u username -p -h localhost sambatan_db
```

#### 2. Upload Directory Permissions
**Problem**: `Failed to upload image`

**Solution**:
```bash
sudo chown -R www-data:www-data uploads/
sudo chmod -R 755 uploads/
```

#### 3. Session Issues
**Problem**: Admin login not working

**Solution**:
```php
// Check PHP session configuration
ini_set('session.gc_maxlifetime', 3600);
session_start();

// Clear sessions
rm -rf /tmp/sess_*
```

#### 4. Payment Gateway Issues
**Problem**: Payment callback not received

**Solution**:
```php
// Check notification URL in payment gateway dashboard
// URL: https://yourdomain.com/payment/notification.php

// Enable logging
error_log("Payment notification: " . file_get_contents('php://input'));

// Verify SSL certificate
curl -I https://yourdomain.com/payment/notification.php
```

#### 5. PWA Not Installing
**Problem**: Install prompt not showing

**Solution**:
```javascript
// Check service worker registration
navigator.serviceWorker.getRegistrations().then(function(registrations) {
    console.log('SW registrations:', registrations);
});

// Verify manifest.json
fetch('/manifest.json').then(response => response.json()).then(console.log);

// Check HTTPS
console.log('Protocol:', window.location.protocol);
```

#### 6. Performance Issues
**Problem**: Website loading slowly

**Solution**:
```bash
# Enable gzip compression
# Add to .htaccess
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

# Enable browser caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

### Debug Mode
```php
// Enable debug mode in config/config.php
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
```

### Health Check
```php
// health-check.php
<?php
$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => [
        'database' => check_database(),
        'uploads' => check_uploads_directory(),
        'sessions' => check_sessions(),
        'memory' => memory_get_usage(true),
        'disk_space' => disk_free_space('/'),
    ]
];

function check_database() {
    try {
        require_once 'config/config.php';
        $pdo->query('SELECT 1');
        return 'healthy';
    } catch (Exception $e) {
        return 'unhealthy: ' . $e->getMessage();
    }
}

function check_uploads_directory() {
    return is_writable('uploads/') ? 'healthy' : 'unhealthy';
}

function check_sessions() {
    return session_status() === PHP_SESSION_ACTIVE ? 'healthy' : 'unhealthy';
}

header('Content-Type: application/json');
echo json_encode($health, JSON_PRETTY_PRINT);
?>
```

---

## 📞 Support

### Kontak Developer
- **Email**: developer@sambatan.com
- **WhatsApp**: +62 812-3456-7890
- **GitHub**: https://github.com/sambatan/website

### Documentation Updates
Dokumentasi ini akan diupdate seiring dengan pengembangan fitur baru. Check GitHub repository untuk versi terbaru.

### Contribution
Kontribusi dalam bentuk bug reports, feature requests, dan pull requests sangat diterima.

---

**© 2024 Sambatan Coffee & Space. All rights reserved.**
