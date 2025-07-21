<?php
// config.php

$db_host = 'localhost';
$db_name = ' '; // Ganti
$db_user = ' '; // Ganti
$db_pass = ' '; // Ganti

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>