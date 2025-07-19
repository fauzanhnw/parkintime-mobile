<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/x-www-form-urlencoded');
require 'db_connection.php';

$email = $_GET['email']; // Misal ID user login dikirim dari Flutter

$query = $mysqli->query("SELECT nama_lengkap FROM profil WHERE email = $email");

if ($row = $query->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'name' => $row['name']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
