<?php
header('Content-Type: application/json');

// Cek metode permintaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ambil parameter ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Validasi ID
if ($id <= 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid car ID']);
    exit;
}

// Koneksi database
require 'db_connection.php';
// Pastikan file ini berisi koneksi ke database ($conn)

// Query hapus
$query = "DELETE FROM kendaraan WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Car deleted successfully']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to delete car']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to prepare statement']);
}

$conn->close();
