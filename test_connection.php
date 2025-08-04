<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Database Connection</h2>";

try {
    // Include config
    require_once 'config/config.php';
    
    echo "✅ Config loaded successfully<br>";
    echo "✅ Database connection successful<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menu_items");
    $result = $stmt->fetch();
    echo "✅ Found {$result['count']} menu items in database<br>";
    
    // Test categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    echo "✅ Found {$result['count']} categories in database<br>";
    
    echo "<br><strong>✅ All tests passed! The application should work now.</strong>";
    
} catch (Exception $e) {
    echo "<span style='color:red'>❌ Error: " . $e->getMessage() . "</span>";
}
?>
