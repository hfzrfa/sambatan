<?php
// Setup Database untuk XAMPP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Database Setup untuk XAMPP</h2>";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Database credentials untuk XAMPP
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sambatan_db';

try {
    echo "<h3>1. Connecting to MySQL Server...</h3>";
    
    // Connect tanpa database dulu
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<span class='success'>✅ Connected to MySQL server</span><br>";
    
    // Create database
    echo "<h3>2. Creating Database...</h3>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<span class='success'>✅ Database '$database' ready</span><br>";
    
    // Connect to database
    $pdo->exec("USE `$database`");
    echo "<span class='success'>✅ Using database '$database'</span><br>";
    
    // Execute SQL file
    echo "<h3>3. Importing Tables...</h3>";
    $sqlFile = 'database/sambatan_db.sql';
    
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Clean and split SQL
        $sql = preg_replace('/--.*$/m', '', $sql); // Remove comments
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $executed = 0;
        foreach ($queries as $query) {
            if (!empty($query) && strlen($query) > 5) {
                try {
                    $pdo->exec($query);
                    $executed++;
                } catch (PDOException $e) {
                    // Skip CREATE DATABASE errors
                    if (strpos($e->getMessage(), 'database exists') === false && 
                        strpos($query, 'CREATE DATABASE') === false &&
                        strpos($query, 'USE ') === false) {
                        echo "<span class='warning'>⚠️ Query warning: " . $e->getMessage() . "</span><br>";
                    }
                }
            }
        }
        
        echo "<span class='success'>✅ Executed $executed SQL queries</span><br>";
        
        // Verify tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<span class='success'>✅ Tables created: " . implode(', ', $tables) . "</span><br>";
            
            // Check admin table and create default admin
            if (in_array('admin', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin");
                $result = $stmt->fetch();
                
                if ($result['count'] == 0) {
                    $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admin (username, password, email) VALUES (?, ?, ?)");
                    $stmt->execute(['admin', $defaultPassword, 'admin@sambatan.com']);
                    echo "<span class='success'>✅ Default admin created</span><br>";
                    echo "<strong>Login:</strong> admin / admin123<br>";
                } else {
                    echo "<span class='success'>✅ Admin account exists</span><br>";
                }
            }
            
        } else {
            echo "<span class='error'>❌ No tables created</span><br>";
        }
        
    } else {
        echo "<span class='error'>❌ SQL file not found: $sqlFile</span><br>";
        echo "Please make sure the database folder exists with sambatan_db.sql<br>";
    }
    
    echo "<h3>✅ Database Setup Complete!</h3>";
    echo "<p>Now try accessing:</p>";
    echo "<a href='index.php' style='background:#28a745;color:white;padding:10px;text-decoration:none;margin-right:10px;'>Website Homepage</a>";
    echo "<a href='admin/login.php' style='background:#007cba;color:white;padding:10px;text-decoration:none;'>Admin Panel</a>";
    
} catch (PDOException $e) {
    echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
    echo "<br><strong>Troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP is running<br>";
    echo "2. Start Apache and MySQL in XAMPP Control Panel<br>";
    echo "3. Check if MySQL is green/running<br>";
    echo "4. Try accessing phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a><br>";
}
?>
