<?php
// Debug file - hanya untuk testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Information</h2>";

// Test database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'config/config.php';
    echo "✅ Database connection successful<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Host: " . DB_HOST . "<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin");
    $result = $stmt->fetch();
    echo "✅ Admin table accessible. Admin count: " . $result['count'] . "<br>";
    
} catch(Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test file permissions
echo "<h3>2. File Permissions Test</h3>";
$testDirs = [
    'uploads/',
    'uploads/menu/',
    'config/',
    'admin/'
];

foreach($testDirs as $dir) {
    if(is_dir($dir)) {
        echo "✅ Directory exists: $dir<br>";
        if(is_writable($dir)) {
            echo "✅ Directory writable: $dir<br>";
        } else {
            echo "❌ Directory not writable: $dir<br>";
        }
    } else {
        echo "❌ Directory missing: $dir<br>";
        // Try to create it
        if(mkdir($dir, 0755, true)) {
            echo "✅ Directory created: $dir<br>";
        } else {
            echo "❌ Failed to create: $dir<br>";
        }
    }
}

// Test PHP version and extensions
echo "<h3>3. PHP Environment Test</h3>";
echo "PHP Version: " . phpversion() . "<br>";

$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'json', 'mbstring'];
foreach($required_extensions as $ext) {
    if(extension_loaded($ext)) {
        echo "✅ Extension loaded: $ext<br>";
    } else {
        echo "❌ Extension missing: $ext<br>";
    }
}

// Test include paths
echo "<h3>4. Include Path Test</h3>";
$files_to_check = [
    'config/config.php',
    'admin/login.php',
    'menu.php',
    'assets/sambatanlogo.png'
];

foreach($files_to_check as $file) {
    if(file_exists($file)) {
        echo "✅ File exists: $file<br>";
    } else {
        echo "❌ File missing: $file<br>";
    }
}

echo "<h3>5. Constants Test</h3>";
if(defined('SITE_URL')) {
    echo "✅ SITE_URL: " . SITE_URL . "<br>";
} else {
    echo "❌ SITE_URL not defined<br>";
}

if(defined('UPLOAD_PATH')) {
    echo "✅ UPLOAD_PATH: " . UPLOAD_PATH . "<br>";
} else {
    echo "❌ UPLOAD_PATH not defined<br>";
}

echo "<hr>";
echo "<p><strong>Setelah semua ✅, hapus file debug.php ini dan coba akses index.php</strong></p>";
?>
