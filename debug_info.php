<?php
// PHP Error Debugging Script
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Sambatan Website Debug Info</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

echo "<h2>1. PHP Configuration</h2>";
echo "PHP Version: <span class='ok'>" . phpversion() . "</span><br>";
echo "Display Errors: <span class='" . (ini_get('display_errors') ? 'ok\'>ON' : 'error\'>OFF') . "</span><br>";
echo "Error Reporting: <span class='ok'>" . error_reporting() . "</span><br>";

echo "<h2>2. Required Extensions</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'gd', 'json'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? 'ok\'>✅' : 'error\'>❌';
    echo "$ext: <span class='$status</span><br>";
}

echo "<h2>3. File Permissions</h2>";
$dirs = ['uploads/', 'uploads/menu/', 'uploads/gallery/', 'logs/'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? 'ok\'>✅ Writable' : 'error\'>❌ Not Writable';
        echo "$dir: <span class='$writable</span><br>";
    } else {
        echo "$dir: <span class='warning'>⚠️ Directory doesn't exist</span><br>";
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;→ <span class='ok'>✅ Created successfully</span><br>";
        }
    }
}

echo "<h2>4. Database Connection</h2>";
try {
    require_once 'config/config.php';
    echo "Database: <span class='ok'>✅ Connected</span><br>";
    
    // Test each table
    $tables = ['admin', 'categories', 'menu_items', 'customers', 'orders'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "$table table: <span class='ok'>✅ $count records</span><br>";
        } catch (Exception $e) {
            echo "$table table: <span class='error'>❌ " . $e->getMessage() . "</span><br>";
        }
    }
} catch (Exception $e) {
    echo "Database: <span class='error'>❌ " . $e->getMessage() . "</span><br>";
}

echo "<h2>5. File Existence Check</h2>";
$critical_files = [
    'index.php',
    'menu.php',
    'config/config.php',
    'admin/login.php',
    'css/styles.css'
];

foreach ($critical_files as $file) {
    $exists = file_exists($file) ? 'ok\'>✅ Exists' : 'error\'>❌ Missing';
    echo "$file: <span class='$exists</span><br>";
}

echo "<h2>6. Test URLs</h2>";
echo "<a href='index.php' target='_blank' style='background:#007cba;color:white;padding:10px;text-decoration:none;margin:5px;'>Test Homepage</a><br><br>";
echo "<a href='menu.php' target='_blank' style='background:#28a745;color:white;padding:10px;text-decoration:none;margin:5px;'>Test Menu Page</a><br><br>";
echo "<a href='admin/login.php' target='_blank' style='background:#dc3545;color:white;padding:10px;text-decoration:none;margin:5px;'>Test Admin Login</a><br><br>";

echo "<hr><h2>✅ Diagnosis Complete</h2>";
echo "<p>If all items above show ✅, your website should be working properly.</p>";
?>
