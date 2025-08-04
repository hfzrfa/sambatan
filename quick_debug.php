<?php
// Simple Debug Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Sambatan Debug</h1>";
echo "<style>body{font-family:Arial;} .ok{color:green;} .error{color:red;}</style>";

echo "<h2>Database Test</h2>";
try {
    require_once 'config/config.php';
    echo "<div class='ok'>✅ Database connected successfully</div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items");
    $count = $stmt->fetchColumn();
    echo "<div class='ok'>✅ Found $count menu items</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "<h2>File Check</h2>";
$files = ['index.php', 'menu.php', 'admin/login.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<div class='ok'>✅ $file exists</div>";
    } else {
        echo "<div class='error'>❌ $file missing</div>";
    }
}

echo "<h2>PHP Info</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Extensions: PDO=" . (extension_loaded('pdo') ? 'Yes' : 'No') . ", MySQL=" . (extension_loaded('pdo_mysql') ? 'Yes' : 'No');

echo "<h2>Quick Test Links</h2>";
echo "<a href='index.php'>Test Homepage</a> | ";
echo "<a href='menu.php'>Test Menu</a> | ";
echo "<a href='admin/login.php'>Test Admin</a>";
?>
