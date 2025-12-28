<?php
require_once 'config/database.php';

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Database connected!";

// Test user creation
$test_user = 'testuser';
$test_email = 'test@example.com';
$test_pass = password_hash('test123', PASSWORD_DEFAULT);

$stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $test_user, $test_email, $test_pass);

if ($stmt->execute()) {
    echo "<br>✅ User creation works!";
} else {
    echo "<br>❌ User creation failed: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>
