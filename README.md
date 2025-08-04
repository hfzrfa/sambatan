# ☕ Sambatan Coffee & Space - Website Resmi

<div align="center">
  <img src="assets/sambatanlogo.png" alt="Sambatan Logo" width="200">
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)](https://mysql.com)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.0-purple.svg)](https://getbootstrap.com)
  [![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
  [![PWA](https://img.shields.io/badge/PWA-Ready-success.svg)](https://web.dev/progressive-web-apps/)
</div>

## 📖 Tentang Proyek

Website resmi **Sambatan Coffee & Space** - platform digital lengkap untuk kedai kopi modern dengan fitur:

- 🌐 **Website Responsif** - Optimal di semua device
- 👑 **Admin Panel** - Dashboard lengkap dengan CRUD operations
- 🛒 **Sistem Pemesanan** - Order online dengan tracking
- 💳 **Payment Gateway** - Integrasi Midtrans & Xendit
- 📱 **PWA Ready** - Installable web app
- 🚀 **Performance Optimized** - Fast loading & SEO friendly

## ✨ Fitur Utama

### 🎯 Frontend Features
- **Responsive Design** - Mobile-first approach
- **Interactive Menu** - Filter by category, search functionality
- **Online Ordering** - Cart system dengan real-time updates
- **Customer Reviews** - Rating dan testimoni
- **Progressive Web App** - Offline support & push notifications
- **AOS Animations** - Smooth scrolling animations
- **Contact Integration** - WhatsApp, maps, social media

### ⚡ Backend Features
- **Admin Dashboard** - Statistics dan analytics
- **Menu Management** - CRUD operations dengan image upload
- **Order Management** - Order tracking dan status updates
- **Customer Management** - Database pelanggan
- **Reports & Analytics** - Sales reports dan insights
- **Settings Panel** - Website configuration

### 🔒 Security Features
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Token-based security
- **Secure File Upload** - Validation & restrictions
- **Session Security** - Secure session management
- **Admin Authentication** - Password hashing

## 🚀 Quick Start

### Windows (XAMPP)
1. **Download & Install XAMPP** dari [apachefriends.org](https://www.apachefriends.org/)
2. **Clone atau download** project ini
3. **Jalankan setup.bat** sebagai Administrator
4. **Buka browser** ke `http://localhost/sambatan`

### Manual Setup
```bash
# 1. Clone repository
git clone https://github.com/yourusername/sambatan-coffee.git

# 2. Pindah ke direktori project
cd sambatan-coffee

# 3. Copy ke web directory
cp -r * /var/www/html/sambatan/

# 4. Set permissions
chmod -R 755 /var/www/html/sambatan/
chmod -R 777 uploads/

# 5. Import database
mysql -u root -p sambatan_db < database/sambatan_db.sql

# 6. Update config
# Edit config/config.php sesuai environment
```

## 📋 Persyaratan Sistem

### Minimum Requirements
- **Web Server**: Apache 2.4+ / Nginx 1.18+
- **PHP**: 8.0+ dengan ekstensi PDO, GD, cURL
- **Database**: MySQL 8.0+ / MariaDB 10.5+
- **Storage**: 2GB free space
- **RAM**: 1GB minimum

### Development Tools
- **Code Editor**: VS Code / PhpStorm
- **Version Control**: Git
- **Local Server**: XAMPP / WAMP / MAMP
- **Database Tool**: phpMyAdmin / MySQL Workbench

## 🗂️ Struktur Proyek

```
sambatan/
├── 📁 admin/                 # Admin panel
│   ├── dashboard.php         # Admin dashboard
│   ├── login.php            # Admin authentication
│   ├── menu.php             # Menu management
│   └── orders.php           # Order management
├── 📁 api/                  # API endpoints
│   ├── menu.php             # Menu API
│   └── orders.php           # Orders API
├── 📁 assets/               # Static assets
│   ├── background.png       # Images
│   └── sambatanlogo.png     # Logo
├── 📁 config/               # Configuration
│   └── config.php           # Database & settings
├── 📁 css/                  # Stylesheets
│   ├── styles.css           # Main styles
│   └── responsive.css       # Mobile styles
├── 📁 database/             # Database files
│   └── sambatan_db.sql      # Database schema
├── 📁 docs/                 # Documentation
│   ├── README.md            # Setup guide
│   └── DEPLOYMENT.md        # Production deployment
├── 📁 js/                   # JavaScript files
│   ├── index.js             # Main JS
│   └── sw.js                # Service Worker
├── 📁 layout/               # HTML components
│   ├── navbar.html          # Navigation
│   └── tentang.html         # About section
├── 📁 uploads/              # Upload directory
│   └── menu/                # Menu images
├── index.php                # Homepage
├── menu.php                 # Menu page
├── order.php                # Order page
├── checkout.php             # Checkout page
├── error.php                # Error handler
├── manifest.json            # PWA manifest
├── .htaccess                # Apache configuration
└── setup.bat                # Windows setup script
```

## 🔧 Konfigurasi

### Database Configuration
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sambatan_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Site Configuration
```php
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'Sambatan Coffee & Space');
define('DEBUG', false); // Set to false for production
```

### Payment Gateway
Update payment credentials di `config/config.php`:
```php
// Midtrans
define('MIDTRANS_SERVER_KEY', 'your_server_key');
define('MIDTRANS_CLIENT_KEY', 'your_client_key');

// Xendit
define('XENDIT_SECRET_KEY', 'your_secret_key');
```

## 📱 PWA Installation

Website ini mendukung Progressive Web App (PWA):

1. **Buka website** di browser mobile
2. **Tap "Add to Home Screen"** pada browser prompt
3. **Icon app** akan muncul di home screen
4. **Nikmati experience** seperti native app

## 🎨 Customization

### Colors & Branding
Edit CSS variables di `css/styles.css`:
```css
:root {
  --primary-color: #8B4513;    /* Coffee brown */
  --secondary-color: #D2691E;  /* Orange */
  --accent-color: #F4A460;     /* Sandy brown */
}
```

### Menu Categories
Tambah kategori baru di admin panel atau database:
```sql
INSERT INTO categories (name, description, image) 
VALUES ('New Category', 'Description', 'image.jpg');
```

### Upload Settings
Configure di `config/config.php`:
```php
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
```

## 🔐 Admin Panel

### Default Login
- **URL**: `yourdomain.com/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

**⚠️ PENTING**: Ganti password default setelah login pertama!

### Admin Features
- 📊 **Dashboard** - Overview statistics
- 🍕 **Menu Management** - Add/edit/delete menu items
- 📋 **Order Management** - View dan manage orders
- 👥 **Customer Data** - Customer information
- ⚙️ **Settings** - Website configuration

## 💳 Payment Integration

### Midtrans Setup
1. Daftar di [Midtrans](https://midtrans.com)
2. Dapatkan Server Key dan Client Key
3. Update credentials di config
4. Test dengan Sandbox mode

### Xendit Setup
1. Daftar di [Xendit](https://xendit.co)
2. Dapatkan Secret Key
3. Setup webhook URLs
4. Configure payment methods

Panduan lengkap ada di `docs/PAYMENT_GATEWAY.md`

## 📈 Performance Optimization

### Implemented Optimizations
- ✅ **Gzip Compression** - Reduced file sizes
- ✅ **Browser Caching** - Static asset caching
- ✅ **Image Optimization** - WebP support
- ✅ **Lazy Loading** - Images loaded on demand
- ✅ **Minified Assets** - CSS/JS compression
- ✅ **CDN Ready** - External resource loading

### Monitoring
- Google PageSpeed Insights
- GTmetrix
- Pingdom
- New Relic (optional)

## 🧪 Testing

### Local Testing
```bash
# Start local server
php -S localhost:8000

# Open browser
http://localhost:8000
```

### Feature Testing
- ✅ Menu display & filtering
- ✅ Order process
- ✅ Admin CRUD operations
- ✅ Responsive design
- ✅ Payment integration
- ✅ PWA functionality

## 📚 Documentation

### Available Docs
- **[Setup Guide](docs/README.md)** - Detailed installation
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment
- **[Payment Guide](docs/PAYMENT_GATEWAY.md)** - Payment integration
- **[API Documentation](docs/API.md)** - API endpoints

### Code Documentation
- PHP DocBlocks
- Inline comments
- Function descriptions
- Configuration notes

## 🤝 Contributing

1. **Fork** the repository
2. **Create** feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** changes (`git commit -m 'Add AmazingFeature'`)
4. **Push** to branch (`git push origin feature/AmazingFeature`)
5. **Open** Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write meaningful commit messages
- Test thoroughly before submitting
- Update documentation

## 🐛 Bug Reports

Jika menemukan bug:

1. **Check** existing issues
2. **Create** new issue dengan detail:
   - Environment (OS, PHP version, etc.)
   - Steps to reproduce
   - Expected vs actual behavior
   - Screenshots jika perlu

## 📄 License

Project ini menggunakan **MIT License**. Lihat [LICENSE](LICENSE) untuk detail.

## 🙏 Acknowledgments

- **Bootstrap** - UI Framework
- **Font Awesome** - Icons
- **AOS** - Animations
- **Midtrans & Xendit** - Payment gateways
- **Unsplash** - Stock photos

## 📞 Support & Contact

- **Website**: [sambatan.com](https://sambatan.com)
- **Email**: support@sambatan.com
- **WhatsApp**: +62 812-3456-7890
- **Instagram**: [@sambatan.coffee](https://instagram.com/sambatan.coffee)

---

<div align="center">
  <p>Made with ❤️ for Sambatan Coffee & Space</p>
  <p>© 2024 Sambatan Coffee & Space. All rights reserved.</p>
</div>
#   s a m b a t a n  
 #   s a m b a t a n  
 #   s a m b a t a n  
 