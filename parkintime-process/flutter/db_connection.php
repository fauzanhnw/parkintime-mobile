<?php
$host     = "localhost";   // Nama host database
$user = " ";        // Username database
$pass = " ";            // Password database
$db = " "; // Ganti dengan nama database Anda


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>