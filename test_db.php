<?php
// Try connecting with different credentials

// Test 1: With root
$test1 = new mysqli('localhost', 'root', '', 'mysql');
if ($test1->connect_error) {
    echo "Test 1 Failed (root): " . $test1->connect_error . "<br>";
} else {
    echo "Test 1 Success (root)!<br>";
    $test1->close();
}

// Test 2: With ecommerce_user
$test2 = new mysqli('localhost', 'ecommerce_user', '', 'ecommerce_user');
if ($test2->connect_error) {
    echo "Test 2 Failed (ecommerce_user): " . $test2->connect_error . "<br>";
} else {
    echo "Test 2 Success (ecommerce_user)!<br>";
    $test2->close();
}

// Test 3: Without password - empty string
$test3 = new mysqli('localhost', 'root', null, 'mysql');
if ($test3->connect_error) {
    echo "Test 3 Failed (root null): " . $test3->connect_error . "<br>";
} else {
    echo "Test 3 Success (root null)!<br>";
    $test3->close();
}

?>
