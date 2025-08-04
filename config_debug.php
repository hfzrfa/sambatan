<?php
// Temporary config with debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!-- Config Debug Start -->\n";

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sambatan_db');

echo "<!-- DB Constants defined -->\n";

// Site configuration
define('SITE_URL', 'http://localhost/sambatan');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

echo "<!-- Site Constants defined -->\n";

// Test database connection
echo "<!-- Testing database connection -->\n";
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "<!-- Database connected successfully -->\n";
} catch(PDOException $e) {
    echo "<!-- Database connection failed: " . $e->getMessage() . " -->\n";
    die("Database connection failed: " . $e->getMessage());
}

// Create uploads directory if not exists
echo "<!-- Creating upload directories -->\n";
$upload_base = dirname(__DIR__) . '/uploads/';
if (!file_exists($upload_base)) {
    mkdir($upload_base, 0755, true);
    echo "<!-- Created upload base directory -->\n";
}
if (!file_exists($upload_base . 'menu/')) {
    mkdir($upload_base . 'menu/', 0755, true);
    echo "<!-- Created menu directory -->\n";
}
if (!file_exists($upload_base . 'gallery/')) {
    mkdir($upload_base . 'gallery/', 0755, true);
    echo "<!-- Created gallery directory -->\n";
}
if (!file_exists($upload_base . 'categories/')) {
    mkdir($upload_base . 'categories/', 0755, true);
    echo "<!-- Created categories directory -->\n";
}

// Helper functions
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function uploadImage($file, $folder = 'menu') {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowed)) {
        return false;
    }
    
    $upload_dir = dirname(__DIR__) . '/uploads/' . $folder . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $newname = uniqid() . '.' . $extension;
    $destination = $upload_dir . $newname;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $folder . '/' . $newname;
    }
    
    return false;
}

echo "<!-- Helper functions defined -->\n";

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<!-- Session started -->\n";
}

echo "<!-- Config Debug End -->\n";
?>
