<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

require 'db_connection.php';

$email = $_POST['email'] ?? '';
$name = $_POST['nama_lengkap'] ?? '';
$alamat = $_POST['alamat'] ?? '';

if (empty($email) || empty($name) || empty($alamat)) {
    echo json_encode(["success" => false, "message" => "Email, name, and address are required"]);
    exit();
}

$stmt = $conn->prepare("UPDATE profil SET nama_lengkap = ?, alamat = ? WHERE email = ?");
$stmt->bind_param("sss", $name, $alamat, $email);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update"]);
}

$conn->close();
?>
