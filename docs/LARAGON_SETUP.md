# 🚀 Panduan Setup Sambatan Coffee di Laragon

## 📖 Tentang Laragon
Laragon adalah development environment yang mudah digunakan untuk Windows, dengan fitur auto virtual host dan SSL yang memudahkan development.

## ⚡ Langkah-langkah Setup

### 1. Persiapan Laragon
1. **Download Laragon** dari [laragon.org](https://laragon.org/download/)
2. **Install Laragon** dengan semua komponen (Apache, MySQL, PHP)
3. **Start Laragon** dan pastikan semua service berjalan

### 2. Struktur Folder Laragon
```
C:\laragon\
├── www/                  # Folder proyek web
│   ├── sambatan/        # Proyek kita akan di sini
│   └── ...
├── bin/
├── etc/
└── ...
```

### 3. Copy Proyek ke Laragon

#### Option A: Copy Manual
1. **Buka folder Laragon**: `C:\laragon\www\`
2. **Buat folder baru**: `sambatan`
3. **Copy semua file** dari proyek ke `C:\laragon\www\sambatan\`

#### Option B: Menggunakan Git (Recommended)
```bash
# Buka terminal Laragon (klik kanan Laragon > Terminal)
cd C:\laragon\www

# Clone atau copy proyek
# Jika ada git repository:
git clone https://github.com/username/sambatan-coffee.git sambatan

# Atau copy dari folder existing
xcopy "E:\CODING\php\sambatan\sambatan\*" "C:\laragon\www\sambatan\" /E /I /Y
```

### 4. Database Setup

#### Menggunakan phpMyAdmin
1. **Buka phpMyAdmin**: `http://localhost/phpmyadmin`
2. **Login** dengan:
   - Username: `root`
   - Password: (kosong)
3. **Buat database baru**: `sambatan_db`
4. **Import file SQL**: 
   - Klik tab "Import"
   - Pilih file `database/sambatan_db.sql`
   - Klik "Go"

#### Menggunakan Command Line
```bash
# Buka terminal Laragon
mysql -u root -e "CREATE DATABASE sambatan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root sambatan_db < C:\laragon\www\sambatan\database\sambatan_db.sql
```

### 5. Konfigurasi Proyek

#### Update Config File
Edit file `C:\laragon\www\sambatan\config\config.php`:

```php
<?php
// Database configuration untuk Laragon
define('DB_HOST', 'localhost');
define('DB_NAME', 'sambatan_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Kosong untuk Laragon default

// Site URL dengan auto virtual host Laragon
define('SITE_URL', 'http://sambatan.test'); // Laragon auto-generates .test domain
define('SITE_NAME', 'Sambatan Coffee & Space');

// Environment
define('DEBUG', true); // Set false untuk production
define('ENVIRONMENT', 'development');

// Sisanya tetap sama...
```

### 6. Set Permissions untuk Upload

#### Windows Command Prompt (Run as Administrator)
```cmd
# Beri akses write ke folder uploads
icacls "C:\laragon\www\sambatan\uploads" /grant Everyone:(OI)(CI)F /T
icacls "C:\laragon\www\sambatan\assets\menu" /grant Everyone:(OI)(CI)F /T
```

### 7. Enable Apache Modules

Laragon biasanya sudah mengaktifkan semua module yang diperlukan, tapi pastikan:

1. **Klik kanan Laragon icon** > Apache > Version > pilih versi terbaru
2. **Pastikan mod_rewrite aktif** (biasanya sudah default)

### 8. Testing Website

1. **Restart Laragon** untuk memastikan semua konfigurasi terbaca
2. **Buka browser** dan akses:
   - Website: `http://sambatan.test`
   - Admin: `http://sambatan.test/admin/login.php`

## 🔧 Auto Virtual Host Laragon

Keunggulan Laragon adalah auto virtual host:

### Default URLs
- `http://sambatan.test` - Website utama
- `http://sambatan.test/admin` - Admin panel
- `http://sambatan.test/phpmyadmin` - Database management

### Custom Domain (Optional)
Jika ingin domain kustom:

1. **Edit hosts file**: `C:\Windows\System32\drivers\etc\hosts`
2. **Tambahkan line**:
   ```
   127.0.0.1 sambatan.local
   127.0.0.1 admin.sambatan.local
   ```
3. **Update config.php**:
   ```php
   define('SITE_URL', 'http://sambatan.local');
   ```

## 🛠️ Troubleshooting

### Issue: Website tidak bisa diakses
**Solusi**:
```bash
# Restart Laragon
# Atau manual restart Apache di Laragon menu
```

### Issue: Database connection error
**Solusi**:
1. Pastikan MySQL service berjalan di Laragon
2. Check credentials di `config/config.php`
3. Test koneksi:
   ```bash
   mysql -u root -e "SELECT 1;"
   ```

### Issue: Upload tidak bekerja
**Solusi**:
```cmd
# Set permissions lagi
icacls "C:\laragon\www\sambatan\uploads" /grant Everyone:(OI)(CI)F /T

# Atau cek PHP upload settings
php -i | grep upload
```

### Issue: .htaccess tidak bekerja
**Solusi**:
1. Pastikan mod_rewrite aktif
2. Check file `.htaccess` ada di root folder
3. Restart Apache

## ⚙️ Konfigurasi Lanjutan

### SSL Setup (HTTPS)
Laragon mendukung SSL otomatis:

1. **Klik kanan Laragon** > SSL > sambatan.test
2. **Update config.php**:
   ```php
   define('SITE_URL', 'https://sambatan.test');
   ```

### PHP Version
Untuk switch PHP version:

1. **Klik kanan Laragon** > PHP > pilih versi
2. **Restart Laragon**

### Multiple Environments
Untuk setup staging/production:

```php
// config/config.php
$environment = $_SERVER['HTTP_HOST'] ?? 'sambatan.test';

switch($environment) {
    case 'sambatan.test':
        define('DEBUG', true);
        define('DB_NAME', 'sambatan_db');
        break;
    case 'staging.sambatan.test':
        define('DEBUG', false);
        define('DB_NAME', 'sambatan_staging');
        break;
}
```

## 📁 Struktur Final di Laragon

```
C:\laragon\www\sambatan\
├── admin/
├── api/
├── assets/
├── config/
├── css/
├── database/
├── docs/
├── js/
├── layout/
├── uploads/
├── index.php
├── menu.php
├── order.php
├── checkout.php
├── .htaccess
└── manifest.json
```

## ✅ Checklist Setup

- [ ] Laragon terinstall dan berjalan
- [ ] Proyek dicopy ke `C:\laragon\www\sambatan\`
- [ ] Database `sambatan_db` dibuat
- [ ] File SQL diimport
- [ ] `config/config.php` diupdate
- [ ] Permissions folder uploads diset
- [ ] Website bisa diakses di `http://sambatan.test`
- [ ] Admin panel bisa diakses
- [ ] Upload gambar berfungsi

## 🎯 Default Access

- **Website**: http://sambatan.test
- **Admin Panel**: http://sambatan.test/admin/login.php
  - Username: `admin`
  - Password: `admin123`
- **phpMyAdmin**: http://localhost/phpmyadmin

## 📞 Support

Jika mengalami kendala:
1. Check Laragon logs di Laragon menu
2. Check Apache/MySQL status
3. Pastikan tidak ada port conflict
4. Restart Laragon dan coba lagi

---

**Selamat! Website Sambatan Coffee siap berjalan di Laragon! ☕**
