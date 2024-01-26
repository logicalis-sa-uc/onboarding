<?php
// Database configuration
$host = '';
$username = '';
$password = '';
$database = '';

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the character set to utf8 (if needed)
$conn->set_charset("utf8");

?>