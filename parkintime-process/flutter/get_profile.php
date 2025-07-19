<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require 'db_connection.php';

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(["success" => false, "message" => "Email required"]);
    exit();
}

// Tambahkan alamat di SELECT
$stmt = $conn->prepare("SELECT nama_lengkap, email, alamat FROM profil WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "User not found"]);
} else {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "data" => $row]);
}

$conn->close();
?>
