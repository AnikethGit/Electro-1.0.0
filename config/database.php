<?php
/**
 * Database Configuration File
 * config/database.php
 */

// Database credentials
define('DB_HOST', 'localhost');      // Hostinger: usually localhost
define('DB_USER', 'root');           // Your MySQL username (change in production)
define('DB_PASS', 'password');               // Your MySQL password
define('DB_NAME', 'ecommerce_db');   // Database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Optional: Set timezone
date_default_timezone_set('UTC');

// Return connection for use in other files
// Usage: include 'config/database.php';
?>