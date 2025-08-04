@echo off
echo ============================================
echo   SAMBATAN COFFEE - LARAGON SETUP SCRIPT
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

echo [INFO] Memulai setup untuk Laragon...
echo.

REM Check if Laragon is installed
if not exist "C:\laragon\laragon.exe" (
    echo ERROR: Laragon tidak ditemukan!
    echo Silakan install Laragon terlebih dahulu dari https://laragon.org/
    pause
    exit /b 1
)

echo [INFO] Laragon ditemukan
echo.

REM Get current directory
set "CURRENT_DIR=%~dp0"
echo [INFO] Source directory: %CURRENT_DIR%

echo [1/7] Menyiapkan direktori proyek...

REM Create project directory in Laragon
if not exist "C:\laragon\www\sambatan" (
    mkdir "C:\laragon\www\sambatan"
    echo [INFO] Direktori C:\laragon\www\sambatan dibuat
)

REM Copy files to Laragon www directory
echo [INFO] Menyalin file proyek ke Laragon...
xcopy "%CURRENT_DIR%*" "C:\laragon\www\sambatan\" /E /I /Y /Q >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] Beberapa file mungkin tidak tersalin. Silakan salin manual jika diperlukan.
) else (
    echo [INFO] File proyek berhasil disalin ke Laragon
)

echo.
echo [2/7] Mengonfigurasi direktori uploads...

REM Create upload directories
if not exist "C:\laragon\www\sambatan\uploads" mkdir "C:\laragon\www\sambatan\uploads"
if not exist "C:\laragon\www\sambatan\uploads\menu" mkdir "C:\laragon\www\sambatan\uploads\menu"
if not exist "C:\laragon\www\sambatan\uploads\temp" mkdir "C:\laragon\www\sambatan\uploads\temp"

REM Set permissions for uploads
icacls "C:\laragon\www\sambatan\uploads" /grant Everyone:(OI)(CI)F /T >nul 2>&1
icacls "C:\laragon\www\sambatan\assets\menu" /grant Everyone:(OI)(CI)F /T >nul 2>&1

echo [INFO] Direktori uploads dikonfigurasi dengan permissions yang benar

echo.
echo [3/7] Memulai Laragon services...

REM Start Laragon
echo [INFO] Memulai Laragon...
start "" "C:\laragon\laragon.exe"

REM Wait for Laragon to start
echo [INFO] Menunggu Laragon startup...
timeout /t 10 /nobreak >nul

REM Check if MySQL is accessible
echo [INFO] Memeriksa koneksi MySQL...
"C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "SELECT 1;" >nul 2>&1
if %errorLevel% neq 0 (
    echo [WARNING] MySQL belum siap. Tunggu sebentar...
    timeout /t 15 /nobreak >nul
)

echo.
echo [4/7] Membuat database...

REM Create database
echo [INFO] Membuat database sambatan_db...
"C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS sambatan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %errorLevel% equ 0 (
    echo [INFO] Database sambatan_db berhasil dibuat
) else (
    echo [ERROR] Gagal membuat database. Periksa koneksi MySQL.
    pause
    exit /b 1
)

REM Import database schema
if exist "C:\laragon\www\sambatan\database\sambatan_db.sql" (
    echo [INFO] Mengimport schema database...
    "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u root sambatan_db < "C:\laragon\www\sambatan\database\sambatan_db.sql"
    if %errorLevel% equ 0 (
        echo [INFO] Database schema berhasil diimport
    ) else (
        echo [WARNING] Import database gagal. Silakan import manual via phpMyAdmin
    )
) else (
    echo [WARNING] File database\sambatan_db.sql tidak ditemukan
)

echo.
echo [5/7] Mengupdate konfigurasi untuk Laragon...

REM Create optimized config for Laragon
if exist "C:\laragon\www\sambatan\config\config.php" (
    echo [INFO] Mengupdate file konfigurasi...
    
    (
        echo ^<?php
        echo /**
        echo  * Sambatan Coffee Configuration for Laragon
        echo  * Optimized for local development
        echo  */
        echo.
        echo // Database configuration for Laragon
        echo define^('DB_HOST', 'localhost'^);
        echo define^('DB_NAME', 'sambatan_db'^);
        echo define^('DB_USER', 'root'^);
        echo define^('DB_PASS', ''^); // Empty for Laragon default
        echo.
        echo // Site configuration with Laragon auto virtual host
        echo define^('SITE_URL', 'http://sambatan.test'^); // Laragon auto-generates .test domain
        echo define^('SITE_NAME', 'Sambatan Coffee ^& Space'^);
        echo.
        echo // Environment settings
        echo define^('DEBUG', true^); // Set to false for production
        echo define^('ENVIRONMENT', 'development'^);
        echo.
        echo // Security keys ^(change these in production^)
        echo define^('ENCRYPTION_KEY', 'laragon-dev-key-' . uniqid^(^)^);
        echo define^('SESSION_PREFIX', 'sambatan_'^);
        echo.
        echo // File upload settings
        echo define^('MAX_FILE_SIZE', 10 * 1024 * 1024^); // 10MB
        echo define^('UPLOAD_PATH', 'uploads/'^);
        echo define^('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']^);
        echo.
        echo // Email settings ^(for development^)
        echo define^('SMTP_HOST', 'localhost'^);
        echo define^('SMTP_PORT', 587^);
        echo define^('SMTP_USER', ''^);
        echo define^('SMTP_PASS', ''^);
        echo define^('FROM_EMAIL', 'noreply@sambatan.test'^);
        echo define^('FROM_NAME', 'Sambatan Coffee'^);
        echo.
        echo // Payment settings ^(sandbox for development^)
        echo define^('MIDTRANS_SERVER_KEY', 'your-sandbox-server-key'^);
        echo define^('MIDTRANS_CLIENT_KEY', 'your-sandbox-client-key'^);
        echo define^('MIDTRANS_IS_PRODUCTION', false^);
        echo.
        echo define^('XENDIT_SECRET_KEY', 'your-test-secret-key'^);
        echo define^('XENDIT_IS_PRODUCTION', false^);
        echo.
        echo // Performance settings for development
        echo define^('CACHE_ENABLED', false^); // Disable cache for development
        echo define^('MINIFY_ASSETS', false^); // Disable minification for development
        echo.
        echo try {
        echo     $pdo = new PDO^("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS^);
        echo     $pdo-^>setAttribute^(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION^);
        echo     $pdo-^>setAttribute^(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC^);
        echo     
        echo     // Set timezone
        echo     $pdo-^>exec^("SET time_zone = '+07:00'"^); // Indonesia timezone
        echo     
        echo } catch ^(PDOException $e^) {
        echo     if ^(DEBUG^) {
        echo         die^("Database connection failed: " . $e-^>getMessage^(^)^);
        echo     } else {
        echo         die^("Database connection failed. Please contact administrator."^);
        echo     }
        echo }
        echo.
        echo // Helper functions
        echo function sanitize^($data^) {
        echo     return htmlspecialchars^(strip_tags^(trim^($data^)^), ENT_QUOTES, 'UTF-8'^);
        echo }
        echo.
        echo function formatPrice^($price^) {
        echo     return "Rp " . number_format^($price, 0, ',', '.''^);
        echo }
        echo.
        echo function uploadImage^($file, $directory = 'uploads/', $allowed = null^) {
        echo     $allowed = $allowed ?? ALLOWED_EXTENSIONS;
        echo     $filename = $file['name'];
        echo     $filesize = $file['size'];
        echo     $filetemp = $file['tmp_name'];
        echo     $error = $file['error'];
        echo     
        echo     // Check for upload errors
        echo     if ^($error !== UPLOAD_ERR_OK^) {
        echo         return ['success' =^> false, 'message' =^> 'Upload error: ' . $error];
        echo     }
        echo     
        echo     $ext = strtolower^(pathinfo^($filename, PATHINFO_EXTENSION^)^);
        echo     
        echo     if ^(!in_array^($ext, $allowed^)^) {
        echo         return ['success' =^> false, 'message' =^> 'Format file tidak didukung. Gunakan: ' . implode^(', ', $allowed^)];
        echo     }
        echo     
        echo     if ^($filesize ^> MAX_FILE_SIZE^) {
        echo         return ['success' =^> false, 'message' =^> 'Ukuran file terlalu besar. Maksimal: ' . ^(MAX_FILE_SIZE / 1024 / 1024^) . 'MB'];
        echo     }
        echo     
        echo     // Create directory if not exists
        echo     if ^(!is_dir^($directory^)^) {
        echo         mkdir^($directory, 0755, true^);
        echo     }
        echo     
        echo     $newname = uniqid^(^) . '_' . time^(^) . '.' . $ext;
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
        echo.
        echo function logError^($message, $file = 'error.log'^) {
        echo     if ^(DEBUG^) {
        echo         $log = "[" . date^('Y-m-d H:i:s'^) . "] " . $message . PHP_EOL;
        echo         file_put_contents^('logs/' . $file, $log, FILE_APPEND ^| LOCK_EX^);
        echo     }
        echo }
        echo.
        echo // Create logs directory
        echo if ^(!is_dir^('logs'^)^) {
        echo     mkdir^('logs', 0755, true^);
        echo }
        echo.
        echo ?^>
    ) > "C:\laragon\www\sambatan\config\config.php"
    
    echo [INFO] Konfigurasi berhasil diupdate untuk Laragon
)

echo.
echo [6/7] Mengecek dan menyiapkan virtual host...

REM Laragon handles virtual hosts automatically
echo [INFO] Laragon akan otomatis membuat virtual host: sambatan.test

echo.
echo [7/7] Finalisasi setup...

REM Create logs directory
if not exist "C:\laragon\www\sambatan\logs" mkdir "C:\laragon\www\sambatan\logs"

REM Set additional permissions
icacls "C:\laragon\www\sambatan\logs" /grant Everyone:(OI)(CI)F /T >nul 2>&1

echo [INFO] Setup selesai!

echo.
echo ============================================
echo             SETUP COMPLETED!
echo ============================================
echo.
echo 🌐 Website URL: http://sambatan.test
echo 👑 Admin Panel: http://sambatan.test/admin/login.php
echo 🗄️ phpMyAdmin: http://localhost/phpmyadmin
echo.
echo 🔑 Default Admin Login:
echo    Username: admin
echo    Password: admin123
echo.
echo 📋 CATATAN PENTING:
echo 1. Pastikan Laragon sudah running (semua service hijau)
echo 2. Tunggu beberapa detik untuk virtual host terbentuk
echo 3. Jika sambatan.test belum bisa diakses, restart Laragon
echo 4. Ubah password admin setelah login pertama
echo 5. Folder uploads sudah dikonfigurasi dengan permissions yang benar
echo.
echo 🔧 Troubleshooting:
echo - Jika website tidak bisa diakses: restart Laragon
echo - Jika database error: cek MySQL service di Laragon
echo - Jika upload error: cek permissions folder uploads
echo.

REM Ask if user wants to open browser
set /p OPEN_BROWSER="Buka website di browser sekarang? (y/n): "
if /i "%OPEN_BROWSER%"=="y" (
    echo [INFO] Membuka browser...
    start http://sambatan.test
    timeout /t 2 /nobreak >nul
    start http://sambatan.test/admin/login.php
)

echo.
echo Tekan tombol apa saja untuk keluar...
pause >nul
