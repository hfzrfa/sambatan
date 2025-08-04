<?php
// Test script untuk semua file admin
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Files Test</h1>";
echo "<style>body{font-family:Arial;} .ok{color:green;} .error{color:red;}</style>";

$admin_files = [
    'admin/login.php',
    'admin/dashboard.php', 
    'admin/menu.php'
];

foreach ($admin_files as $file) {
    echo "<h3>Testing: $file</h3>";
    
    // Test syntax
    $output = [];
    $return_var = 0;
    exec("php -l $file 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<div class='ok'>✅ Syntax OK</div>";
    } else {
        echo "<div class='error'>❌ Syntax Error:</div>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
    
    // Test file execution (just include check)
    try {
        ob_start();
        // Simulate web environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/sambatan/' . $file;
        
        include $file;
        $content = ob_get_clean();
        
        if (strlen($content) > 0) {
            echo "<div class='ok'>✅ File can be included successfully</div>";
        } else {
            echo "<div class='error'>❌ File produces no output</div>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<div class='error'>❌ Include Error: " . $e->getMessage() . "</div>";
    }
    
    echo "<hr>";
}

echo "<h2>Test URL Links</h2>";
echo "<a href='admin/login.php' target='_blank'>Test Admin Login</a><br>";
echo "<a href='index.php' target='_blank'>Test Homepage</a><br>";
echo "<a href='menu.php' target='_blank'>Test Menu</a><br>";
?>
