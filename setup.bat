@echo off
echo ============================================
echo    SAMBATAN COFFEE WEBSITE SETUP SCRIPT
echo ============================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Script harus dijalankan sebagai Administrator!
    echo Klik kanan pada file ini dan pilih "Run as administrator"
    pause
    exit /b 1
)

echo [1/8] Memeriksa persyaratan sistem...

REM Check if XAMPP is installed
if not exist "C:\xampp\xampp-control.exe" (
    echo ERROR: XAMPP tidak ditemukan!
    echo Silakan install XAMPP terlebih dahulu dari https://www.apachefriends.org/
    pause
    exit /b 1
)

echo [INFO] XAMPP ditemukan
echo.

REM Check current directory
set "CURRENT_DIR=%~dp0"
echo [INFO] Setup directory: %CURRENT_DIR%

echo [2/8] Menyiapkan direktori website...

REM Create htdocs directory if not exists
if not exist "C:\xampp\htdocs\sambatan" (
    mkdir "C:\xampp\htdocs\sambatan"
    echo [INFO] Direktori C:\xampp\htdocs\sambatan dibuat
)

REM Copy files to htdocs
echo [INFO] Menyalin file website...
xcopy "%CURRENT_DIR%*" "C:\xampp\htdocs\sambatan\" /E /I /Y /Q >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] Beberapa file mungkin tidak tersalin. Silakan salin manual jika diperlukan.
) else (
    echo [INFO] File website berhasil disalin
)

echo.
echo [3/8] Mengonfigurasi direktori uploads...

REM Create upload directories
if not exist "C:\xampp\htdocs\sambatan\uploads" mkdir "C:\xampp\htdocs\sambatan\uploads"
if not exist "C:\xampp\htdocs\sambatan\uploads\menu" mkdir "C:\xampp\htdocs\sambatan\uploads\menu"
if not exist "C:\xampp\htdocs\sambatan\uploads\temp" mkdir "C:\xampp\htdocs\sambatan\uploads\temp"

REM Set permissions (Windows equivalent)
icacls "C:\xampp\htdocs\sambatan\uploads" /grant Everyone:(OI)(CI)F /T >nul 2>&1
icacls "C:\xampp\htdocs\sambatan\assets\menu" /grant Everyone:(OI)(CI)F /T >nul 2>&1

echo [INFO] Direktori uploads dikonfigurasi

echo.
echo [4/8] Memulai XAMPP services...

REM Start XAMPP services
"C:\xampp\xampp-control.exe" /start

REM Wait a moment for services to start
timeout /t 5 /nobreak >nul

echo [INFO] XAMPP services dimulai

echo.
echo [5/8] Memeriksa koneksi MySQL...

REM Test MySQL connection
"C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] MySQL belum siap. Memulai MySQL...
    "C:\xampp\mysql\bin\mysqld.exe" --install-manual >nul 2>&1
    net start mysql >nul 2>&1
    timeout /t 10 /nobreak >nul
)

echo [INFO] MySQL ready

echo.
echo [6/8] Membuat database...

REM Create database
echo [INFO] Membuat database sambatan_db...
"C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS sambatan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Import database schema
if exist "%CURRENT_DIR%database\sambatan_db.sql" (
    echo [INFO] Mengimport schema database...
    "C:\xampp\mysql\bin\mysql.exe" -u root sambatan_db < "%CURRENT_DIR%database\sambatan_db.sql"
    if %errorLevel% equ 0 (
        echo [INFO] Database berhasil diimport
    ) else (
        echo [WARNING] Import database gagal. Silakan import manual dari phpMyAdmin
    )
) else (
    echo [WARNING] File database\sambatan_db.sql tidak ditemukan
)

echo.
echo [7/8] Konfigurasi file config...

REM Update config file
if exist "C:\xampp\htdocs\sambatan\config\config.php" (
    echo [INFO] Mengupdate konfigurasi...
    
    REM Create temporary config file with correct settings
    (
        echo ^<?php
        echo // Database configuration for local development
        echo define^('DB_HOST', 'localhost'^);
        echo define^('DB_NAME', 'sambatan_db'^);
        echo define^('DB_USER', 'root'^);
        echo define^('DB_PASS', ''^);
        echo.
        echo // Site configuration
        echo define^('SITE_URL', 'http://localhost/sambatan'^);
        echo define^('SITE_NAME', 'Sambatan Coffee ^& Space'^);
        echo.
        echo // Environment
        echo define^('DEBUG', true^);
        echo define^('ENVIRONMENT', 'development'^);
        echo.
        echo // Security keys ^(change these in production^)
        echo define^('ENCRYPTION_KEY', 'your-secret-key-here'^);
        echo define^('SESSION_PREFIX', 'sambatan_'^);
        echo.
        echo // File upload settings
        echo define^('MAX_FILE_SIZE', 10 * 1024 * 1024^); // 10MB
        echo define^('UPLOAD_PATH', 'uploads/'^);
        echo.
        echo // Email settings ^(configure for production^)
        echo define^('SMTP_HOST', 'localhost'^);
        echo define^('SMTP_PORT', 587^);
        echo define^('SMTP_USER', ''^);
        echo define^('SMTP_PASS', ''^);
        echo define^('FROM_EMAIL', 'noreply@sambatan.com'^);
        echo define^('FROM_NAME', 'Sambatan Coffee'^);
        echo.
        echo try {
        echo     $pdo = new PDO^("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS^);
        echo     $pdo-^>setAttribute^(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION^);
        echo     $pdo-^>setAttribute^(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC^);
        echo } catch ^(PDOException $e^) {
        echo     if ^(DEBUG^) {
        echo         die^("Database connection failed: " . $e-^>getMessage^(^)^);
        echo     } else {
        echo         die^("Database connection failed"^);
        echo     }
        echo }
        echo.
        echo // Helper functions
        echo function sanitize^($data^) {
        echo     return htmlspecialchars^(strip_tags^(trim^($data^)^)^);
        echo }
        echo.
        echo function formatPrice^($price^) {
        echo     return "Rp " . number_format^($price, 0, ',', '.''^);
        echo }
        echo.
        echo function uploadImage^($file, $directory = 'uploads/'^) {
        echo     $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        echo     $filename = $file['name'];
        echo     $filesize = $file['size'];
        echo     $filetemp = $file['tmp_name'];
        echo     
        echo     $ext = strtolower^(pathinfo^($filename, PATHINFO_EXTENSION^)^);
        echo     
        echo     if ^(!in_array^($ext, $allowed^)^) {
        echo         return ['success' =^> false, 'message' =^> 'Format file tidak didukung'];
        echo     }
        echo     
        echo     if ^($filesize ^> MAX_FILE_SIZE^) {
        echo         return ['success' =^> false, 'message' =^> 'Ukuran file terlalu besar'];
        echo     }
        echo     
        echo     $newname = uniqid^(^) . '.' . $ext;
        echo     $destination = $directory . $newname;
        echo     
        echo     if ^(move_uploaded_file^($filetemp, $destination^)^) {
        echo         return ['success' =^> true, 'filename' =^> $newname, 'path' =^> $destination];
        echo     } else {
        echo         return ['success' =^> false, 'message' =^> 'Gagal mengupload file'];
        echo     }
        echo }
        echo.
        echo function generateOrderNumber^(^) {
        echo     return 'SBT' . date^('Ymd'^) . str_pad^(rand^(1, 9999^), 4, '0', STR_PAD_LEFT^);
        echo }
        echo ?^>
    ) > "C:\xampp\htdocs\sambatan\config\config.php"
    
    echo [INFO] File config.php berhasil diupdate
) else (
    echo [WARNING] File config.php tidak ditemukan
)

echo.
echo [8/8] Finalisasi setup...

REM Create .htaccess file
if not exist "C:\xampp\htdocs\sambatan\.htaccess" (
    if exist "%CURRENT_DIR%.htaccess" (
        copy "%CURRENT_DIR%.htaccess" "C:\xampp\htdocs\sambatan\.htaccess" >nul
        echo [INFO] File .htaccess disalin
    )
)

REM Enable Apache mod_rewrite (XAMPP usually has this enabled)
echo [INFO] Pastikan mod_rewrite enabled di Apache

REM Final checks
echo.
echo ============================================
echo             SETUP COMPLETED!
echo ============================================
echo.
echo Website URL: http://localhost/sambatan
echo Admin Panel: http://localhost/sambatan/admin/login.php
echo phpMyAdmin: http://localhost/phpmyadmin
echo.
echo Default Admin Login:
echo Username: admin
echo Password: admin123
echo.
echo CATATAN PENTING:
echo 1. Pastikan XAMPP Apache dan MySQL berjalan
echo 2. Ubah password admin setelah login pertama
echo 3. Konfigurasi email settings untuk production
echo 4. Backup database secara berkala
echo.

REM Ask if user wants to open browser
set /p OPEN_BROWSER="Buka website di browser? (y/n): "
if /i "%OPEN_BROWSER%"=="y" (
    start http://localhost/sambatan
)

echo.
echo Tekan tombol apa saja untuk keluar...
pause >nul
