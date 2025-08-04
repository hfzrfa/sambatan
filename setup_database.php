<?php
// Script untuk setup database
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Setup Script</h2>";

// Database credentials
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sambatan_db';

try {
    // Connect to MySQL server (without selecting database first)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL server<br>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '$database' created/verified<br>";
    
    // Select the database
    $pdo->exec("USE `$database`");
    echo "✅ Using database '$database'<br>";
    
    // Read and execute SQL file
    $sqlFile = 'database/sambatan_db.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split into individual queries
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($queries as $query) {
            if (!empty($query) && !strpos($query, '--') === 0) {
                try {
                    $pdo->exec($query);
                } catch (PDOException $e) {
                    // Skip errors for DROP/CREATE DATABASE statements
                    if (strpos($e->getMessage(), 'database exists') === false) {
                        echo "⚠️ Query warning: " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        
        echo "✅ SQL file executed successfully<br>";
        
        // Verify tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Tables created:</h3>";
        foreach ($tables as $table) {
            echo "- $table<br>";
        }
        
        // Check if admin exists
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin");
            $result = $stmt->fetch();
            echo "<br>Admin records: " . $result['count'] . "<br>";
            
            if ($result['count'] == 0) {
                // Insert default admin
                $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin (username, password, email) VALUES (?, ?, ?)");
                $stmt->execute(['admin', $defaultPassword, 'admin@sambatan.com']);
                echo "✅ Default admin created (username: admin, password: admin123)<br>";
            }
        } catch (PDOException $e) {
            echo "⚠️ Admin table issue: " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "❌ SQL file not found: $sqlFile<br>";
    }
    
    echo "<hr>";
    echo "<h3>Database setup completed!</h3>";
    echo "<p>You can now access your website at: <a href='index.php'>index.php</a></p>";
    echo "<p>Admin panel: <a href='admin/login.php'>admin/login.php</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    echo "<br><strong>Common solutions:</strong><br>";
    echo "1. Make sure MySQL/MariaDB is running<br>";
    echo "2. Check database credentials<br>";
    echo "3. Ensure you have proper permissions<br>";
}
?>
