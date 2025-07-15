<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Default XAMPP username
define('DB_PASS', '');     // Default XAMPP password is empty
define('DB_NAME', 'serenetripslk'); // Must match exactly // Note: Case-sensitive if on Linux

// Error reporting (enable during development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Check if PDO extension is loaded
if (!extension_loaded('pdo')) {
    die("PDO extension is not enabled. Please enable it in your php.ini file.");
}

// Check if PDO MySQL driver is available
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    die("PDO MySQL driver is not available. Please install php-mysql package.");
}

// Establish database connection with enhanced error handling
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
    
    // Test connection immediately
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
    
    // Log to file
    error_log($error_message);
    
    // User-friendly message (don't expose details in production)
    die("Could not connect to the database. Please try again later.");
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

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    if (!session_start()) {
        error_log("Failed to start session");
        die("Session initialization failed");
    }
}

// Regenerate session ID to prevent fixation
if (empty($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Check if user is logged in (for admin pages)
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           !empty($_SESSION['admin_user_id']) &&
           $_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR'] &&
           $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}
?>