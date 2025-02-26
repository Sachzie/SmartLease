<?php
$servername = "localhost"; // Replace with your server name if different
$username = "root";        // Replace with your MySQL username
$password = "";            // Replace with your MySQL password
$database = "Smartlease"; // Database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
