<?php

// Mengatur header untuk CORS dan tipe konten
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Memuat koneksi database
require_once 'db_connection.php';

/**
 * Fungsi untuk mengirim response JSON.
 */
function send_json_response(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// --- LOGIKA UTAMA API ---

// 1. Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(405, ['success' => false, 'message' => 'Method not allowed. Please use POST.']);
}

// 2. Validasi input yang dibutuhkan: id_akun dan status
if (!isset($_POST['id_akun']) || !isset($_POST['status'])) {
    send_json_response(400, ['success' => false, 'message' => "Parameters 'id_akun' and 'status' are required."]);
}

$id_akun = $_POST['id_akun'];
$status_to_clear = strtolower($_POST['status']); // e.g., 'completed' or 'canceled'

// 3. Validasi status yang diizinkan untuk dihapus
$allowed_statuses = ['completed', 'canceled'];
if (!in_array($status_to_clear, $allowed_statuses)) {
    send_json_response(400, ['success' => false, 'message' => 'Invalid status. Only "completed" or "canceled" history can be cleared.']);
}

// 4. Cek koneksi database
if ($conn->connect_error) {
    send_json_response(500, ['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
}

// 5. Siapkan dan jalankan query DELETE
// PENTING: Query ini menghapus semua tiket milik id_akun tertentu dengan status tertentu.
$sql = "DELETE FROM tiket WHERE id_akun = ? AND status = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    send_json_response(500, ['success' => false, 'message' => 'Failed to prepare query: ' . $conn->error]);
}

$stmt->bind_param("is", $id_akun, $status_to_clear);

// 6. Proses hasil eksekusi
if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;
    send_json_response(200, [
        'success' => true,
        'message' => "$affected_rows history items have been cleared successfully."
    ]);
} else {
    send_json_response(500, [
        'success' => false,
        'message' => 'Failed to execute clear history query: ' . $stmt->error
    ]);
}

// 7. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>
