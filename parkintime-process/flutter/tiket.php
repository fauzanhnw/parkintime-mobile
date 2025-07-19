<?php

// Set header response ke format JSON
header('Content-Type: application/json');

// Memanggil file koneksi. Script akan mati jika file tidak ditemukan.
require_once 'db_connection.php';

/**
 * Fungsi untuk mengirim response JSON dan mengatur HTTP status code.
 *
 * @param int $statusCode HTTP status code (e.g., 200, 404).
 * @param array $data Data yang akan di-encode ke JSON.
 */
function send_json_response(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit; // Menghentikan eksekusi script setelah mengirim response
}

// --- LOGIKA UTAMA API ---

// 1. Cek koneksi database
if ($conn->connect_error) {
    send_json_response(500, [
        'status' => 'error',
        'message' => 'Gagal terhubung ke database: ' . $conn->connect_error
    ]);
}

// 2. Validasi input ID tiket
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    send_json_response(400, [ // 400 Bad Request
        'status' => 'error',
        'message' => "Parameter 'id' tiket yang valid dibutuhkan."
    ]);
}
$ticket_id = (int)$_GET['id'];

// 3. Query SQL dengan JOIN yang sudah diperbaiki
// [PERUBAHAN] Menambahkan `t.redirect_url` ke dalam daftar SELECT
$sql = "SELECT 
            t.id AS ticket_id,
            t.order_id,
            t.status,
            t.status_pembayaran,
            t.waktu_masuk,
            t.waktu_keluar,
            t.biaya_total,
            t.redirect_url, -- Kolom baru yang diambil
            k.no_kendaraan,
            k.merek,
            k.tipe AS tipe_kendaraan,
            k.kategori AS jenis_kendaraan,
            lp.nama_lokasi AS parking_area,
            lp.alamat,
            lp.tarif_per_jam,
            sp.kode_slot AS parking_spot_code

        FROM 
            tiket AS t
        JOIN 
            kendaraan AS k ON t.id_kendaraan = k.id
        JOIN 
            slot_parkir AS sp ON t.id_slot = sp.id -- Hubungkan tiket ke slot
        JOIN 
            lahan_parkir AS lp ON sp.id_lahan = lp.id -- Hubungkan slot ke lahan
        WHERE 
            t.id = ?
        LIMIT 1";

// 4. Gunakan prepared statement untuk keamanan
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    send_json_response(500, [
        'status' => 'error',
        'message' => 'Gagal mempersiapkan query: ' . $conn->error
    ]);
}

$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

// 5. Proses hasil query
if ($result->num_rows > 0) {
    $ticket_data = $result->fetch_assoc();

    // Format data untuk response
    // [PERUBAHAN] Menambahkan `redirect_url` ke dalam array response
    $formatted_data = [
        'order_id' => $ticket_data['order_id'],
        'status' => $ticket_data['status'],
        'status_pembayaran' => $ticket_data['status_pembayaran'],
        'redirect_url' => $ticket_data['redirect_url'], // URL pembayaran ditambahkan di sini
        'qr_data' => $ticket_data['order_id'],
        'nomor_plat' => $ticket_data['no_kendaraan'],
        'jenis_kendaraan' => $ticket_data['jenis_kendaraan'],
        'vehicle' => $ticket_data['merek'] . ' ' . $ticket_data['tipe_kendaraan'],
        'parking_area' => $ticket_data['parking_area'],
        'address' => $ticket_data['alamat'],
        'parking_spot' => $ticket_data['parking_spot_code'],
        'waktu_masuk' => date("d F Y, H:i", strtotime($ticket_data['waktu_masuk'])),
        'tarif_per_jam' => 'Rp ' . number_format($ticket_data['tarif_per_jam'] ?? 0, 0, ',', '.'),
        'total' => 'Rp ' . number_format($ticket_data['biaya_total'] ?? 0, 0, ',', '.')
    ];

    send_json_response(200, [ // 200 OK
        'status' => 'success',
        'data' => $formatted_data
    ]);
} else {
    // Jika tiket tidak ditemukan
    send_json_response(404, [ // 404 Not Found
        'status' => 'error',
        'message' => 'Tiket dengan ID ' . $ticket_id . ' tidak ditemukan.'
    ]);
}

// 6. Tutup statement dan koneksi
$stmt->close();
$conn->close();

?>
