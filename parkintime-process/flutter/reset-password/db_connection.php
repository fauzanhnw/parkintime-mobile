<?php
// config.php

$db_host = 'localhost';
$db_name = 'irunnlvu_parkintime'; // Ganti
$db_user = 'irunnlvu'; // Ganti
$db_pass = '1.26Z:GoEir5Yj'; // Ganti

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>