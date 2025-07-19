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

// 2. Validasi input yang dibutuhkan: id_akun dan ticket_id
if (!isset($_POST['id_akun']) || !isset($_POST['ticket_id'])) {
    send_json_response(400, ['success' => false, 'message' => "Parameters 'id_akun' and 'ticket_id' are required."]);
}

$id_akun = $_POST['id_akun'];
$ticket_id = $_POST['ticket_id'];

// Validasi bahwa ticket_id adalah integer
if (!filter_var($ticket_id, FILTER_VALIDATE_INT)) {
    send_json_response(400, ['success' => false, 'message' => 'Invalid ticket_id. It must be an integer.']);
}

// 3. Cek koneksi database
if ($conn->connect_error) {
    send_json_response(500, ['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
}

// 4. Siapkan dan jalankan query DELETE
// PENTING: Query ini menghapus tiket berdasarkan ID tiket DAN ID akun.
// Ini adalah pengaman agar pengguna tidak bisa menghapus tiket milik orang lain.
$sql = "DELETE FROM tiket WHERE id = ? AND id_akun = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    send_json_response(500, ['success' => false, 'message' => 'Failed to prepare query: ' . $conn->error]);
}

$stmt->bind_param("ii", $ticket_id, $id_akun);

// 5. Proses hasil eksekusi
if ($stmt->execute()) {
    // Cek apakah ada baris yang terpengaruh (berhasil dihapus)
    if ($stmt->affected_rows > 0) {
        send_json_response(200, [
            'success' => true,
            'message' => 'History item successfully deleted.'
        ]);
    } else {
        // Tidak ada baris yang terhapus, mungkin karena ID tidak cocok atau bukan milik akun tersebut
        send_json_response(404, [
            'success' => false,
            'message' => 'History item not found or you do not have permission to delete it.'
        ]);
    }
} else {
    // Gagal menjalankan query
    send_json_response(500, [
        'success' => false,
        'message' => 'Failed to execute delete query: ' . $stmt->error
    ]);
}

// 6. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>
