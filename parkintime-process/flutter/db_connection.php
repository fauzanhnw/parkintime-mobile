<?php
$host     = "localhost";   // Nama host database
$user = "irunnlvu";        // Username database
$pass = "1.26Z:GoEir5Yj";            // Password database
$db = "irunnlvu_parkintime"; // Ganti dengan nama database Anda


$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>