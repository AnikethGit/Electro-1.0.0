<?php
// This test tells us who phpMyAdmin is logged in as

// Try connecting with ecommerce_user
$test = new mysqli('localhost', 'ecommerce_user', '', 'ecommerce_user');

if ($test->connect_error) {
    echo "❌ Failed: " . $test->connect_error;
} else {
    echo "✅ Success! User 'ecommerce_user' works!";
    
    // Show database info
    $result = $test->query("SELECT DATABASE() as current_db");
    $row = $result->fetch_assoc();
    echo "<br>Current Database: " . $row['current_db'];
    
    $test->close();
}
?>
