<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');     
define('DB_NAME', 'serenetripslk'); 

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Check PDO extension
if (!extension_loaded('pdo')) {
    die("PDO extension is not enabled.");
}

// Check PDO MySQL driver
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    die("PDO MySQL driver is not available.");
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    $pdo->query("SELECT 1");
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Could not connect to the database.");
}

// Session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE && !session_start()) {
    error_log("Failed to start session");
    die("Session initialization failed");
}

if (empty($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Login check function
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           !empty($_SESSION['admin_user_id']) &&
           $_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR'] &&
           $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}
?>