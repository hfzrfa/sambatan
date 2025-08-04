<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sambatan_db');

// Site configuration
define('SITE_URL', 'http://localhost/sambatan');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Don't show database details in production
    die("Database connection failed. Please check your configuration.");
}

// Create uploads directory if not exists
$upload_base = dirname(__DIR__) . '/uploads/';
if (!file_exists($upload_base)) {
    mkdir($upload_base, 0755, true);
}
if (!file_exists($upload_base . 'menu/')) {
    mkdir($upload_base . 'menu/', 0755, true);
}
if (!file_exists($upload_base . 'gallery/')) {
    mkdir($upload_base . 'gallery/', 0755, true);
}
if (!file_exists($upload_base . 'categories/')) {
    mkdir($upload_base . 'categories/', 0755, true);
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

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
