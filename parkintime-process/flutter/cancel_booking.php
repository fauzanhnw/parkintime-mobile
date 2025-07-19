<?php

// Mengatur header untuk CORS dan tipe konten
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Memuat koneksi database
require_once 'db_connection.php';

/**
 * Fungsi untuk mengirim response JSON dan mengatur HTTP status code.
 *
 * @param int $statusCode HTTP status code.
 * @param array $data Data yang akan di-encode ke JSON.
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
    send_json_response(405, [ // 405 Method Not Allowed
        'success' => false, 
        'message' => 'Metode request tidak valid. Gunakan POST.'
    ]);
}

// 2. Validasi input order_id
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    send_json_response(400, [ // 400 Bad Request
        'success' => false, 
        'message' => "Parameter 'order_id' dibutuhkan."
    ]);
}
$order_id = $_POST['order_id'];

// 3. Cek koneksi database
if ($conn->connect_error) {
    send_json_response(500, [
        'success' => false,
        'message' => 'Gagal terhubung ke database: ' . $conn->connect_error
    ]);
}

// 4. Siapkan dan jalankan query UPDATE
// Query ini akan mengubah status tiket menjadi 'canceled' HANYA JIKA status saat ini adalah 'pending'
$sql = "UPDATE tiket SET status = 'canceled' WHERE order_id = ? AND status = 'pending'";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    send_json_response(500, [
        'success' => false,
        'message' => 'Gagal mempersiapkan query: ' . $conn->error
    ]);
}

$stmt->bind_param("s", $order_id);

// 5. Proses hasil eksekusi
if ($stmt->execute()) {
    // Cek apakah ada baris yang terpengaruh (berhasil di-update)
    if ($stmt->affected_rows > 0) {
        send_json_response(200, [ // 200 OK
            'success' => true,
            'message' => 'Pesanan dengan Order ID ' . $order_id . ' berhasil dibatalkan.'
        ]);
    } else {
        // Tidak ada baris yang ter-update, mungkin karena statusnya bukan 'pending' atau order_id tidak ada
        send_json_response(404, [ // 404 Not Found
            'success' => false,
            'message' => 'Pesanan tidak ditemukan atau statusnya bukan "pending".'
        ]);
    }
} else {
    // Gagal menjalankan query
    send_json_response(500, [
        'success' => false,
        'message' => 'Gagal menjalankan query pembatalan: ' . $stmt->error
    ]);
}

// 6. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>
