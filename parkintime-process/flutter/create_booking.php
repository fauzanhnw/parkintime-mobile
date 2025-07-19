<?php
// Mengaktifkan pelaporan error untuk debugging (HAPUS ATAU KOMENTARI DI LINGKUNGAN PRODUKSI)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mengatur header untuk CORS dan tipe konten
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// --- Langkah 1: Memuat Koneksi & Library ---
require_once 'db_connection.php';
require_once dirname(__FILE__) . '/midtrans-php-master/Midtrans.php';

/**
 * Fungsi untuk mengirim response JSON dan mengatur HTTP status code.
 *
 * @param int $statusCode HTTP status code (e.g., 200, 400, 500).
 * @param array $data Data yang akan di-encode ke JSON.
 */
function send_json_response(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit; // Menghentikan eksekusi script
}

// --- Langkah 2: Konfigurasi Midtrans ---
Midtrans\Config::$serverKey = 'SB-Mid-server-3IGYwrZnj6inRjgWy2LV09Ag'; 
Midtrans\Config::$isProduction = false;
Midtrans\Config::$isSanitized = true;
Midtrans\Config::$is3ds = true;

// --- Langkah 3: Mengambil dan Memvalidasi Input ---
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id_akun'], $data['id_kendaraan'], $data['id_slot'], $data['biaya_total'], $data['waktu_masuk'], $data['waktu_keluar'])) {
    send_json_response(400, ['success' => false, 'message' => 'Data input tidak lengkap. Pastikan id_akun, id_kendaraan, id_slot, biaya_total, waktu_masuk, dan waktu_keluar terkirim.']);
}

// --- Langkah 4: Menyiapkan Data dari Input ---
$id_akun = $data['id_akun'];
$id_kendaraan = $data['id_kendaraan'];
$kode_slot_diterima = $data['id_slot'];
$biaya_total = (int)$data['biaya_total'];
$waktu_masuk = $data['waktu_masuk'];
$waktu_keluar = $data['waktu_keluar'];

// --- Langkah 5: Mengambil Detail Pelanggan & Slot ---
$stmt_get_akun = $conn->prepare("SELECT email FROM akun WHERE id = ?");
$stmt_get_akun->bind_param("i", $id_akun);
$stmt_get_akun->execute();
$result_akun = $stmt_get_akun->get_result();
if ($result_akun->num_rows === 0) {
    send_json_response(404, ['success' => false, 'message' => 'Akun pelanggan tidak ditemukan.']);
}
$customer_email = $result_akun->fetch_assoc()['email'];
$customer_name = ucfirst(explode('@', $customer_email)[0]);
$stmt_get_akun->close();

$stmt_get_id = $conn->prepare("SELECT id, status FROM slot_parkir WHERE kode_slot = ?");
$stmt_get_id->bind_param("s", $kode_slot_diterima);
$stmt_get_id->execute();
$result = $stmt_get_id->get_result();
if ($result->num_rows === 0) {
    send_json_response(404, ['success' => false, 'message' => 'Kode slot parkir tidak valid.']);
}
$slot_data = $result->fetch_assoc();
$id_slot_numerik = $slot_data['id'];
if (strtolower($slot_data['status']) !== 'available') {
    send_json_response(409, ['success' => false, 'message' => 'Slot parkir sudah terisi atau tidak tersedia.']);
}
$stmt_get_id->close();

// --- Langkah 6: Memulai Transaksi Database ---
$conn->begin_transaction();

try {
    // Menyiapkan data untuk tabel tiket
    $order_id = 'PARKINTIME-' . time();
    $status = 'pending';
    $status_pembayaran = 'pending';

    $sql = "INSERT INTO tiket (id_akun, id_kendaraan, id_slot, order_id, status, status_pembayaran, waktu_masuk, waktu_keluar, biaya_total) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Gagal menyiapkan query database: ' . $conn->error);
    }

    $stmt->bind_param("iiisssssd", $id_akun, $id_kendaraan, $id_slot_numerik, $order_id, $status, $status_pembayaran, $waktu_masuk, $waktu_keluar, $biaya_total);

    if (!$stmt->execute()) {
        throw new Exception('Gagal menyimpan tiket ke database: ' . $stmt->error);
    }
    $stmt->close();

    // --- Langkah 7: Membuat Transaksi Midtrans ---
    $transaction_details = ['order_id' => $order_id, 'gross_amount' => $biaya_total];
    $customer_details = ['first_name' => $customer_name, 'email' => $customer_email];
    $callbacks = ['finish' => 'https://app.parkintime.web.id/payment/finish.php'];

    $midtrans_transaction = Midtrans\Snap::createTransaction([
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details,
        'callbacks' => $callbacks
    ]);

    // [PERUBAHAN] Menyimpan redirect_url ke database setelah mendapatkannya
    $redirect_url = $midtrans_transaction->redirect_url;
    $stmt_update_url = $conn->prepare("UPDATE tiket SET redirect_url = ? WHERE order_id = ?");
    if ($stmt_update_url === false) {
        throw new Exception('Gagal menyiapkan query update URL: ' . $conn->error);
    }
    $stmt_update_url->bind_param("ss", $redirect_url, $order_id);
    if (!$stmt_update_url->execute()) {
        throw new Exception('Gagal menyimpan redirect_url: ' . $stmt_update_url->error);
    }
    $stmt_update_url->close();

    // Jika semua berhasil, commit transaksi database
    $conn->commit();

    // Kirim response sukses ke aplikasi
    send_json_response(201, [
        'success' => true,
        'message' => 'Tiket berhasil dibuat dan transaksi Midtrans siap.',
        'order_id' => $order_id,
        'redirect_url' => $redirect_url // Menggunakan variabel yang sudah disimpan
    ]);

} catch (Exception $e) {
    // Jika terjadi error di mana pun dalam blok try, batalkan semua perubahan database
    $conn->rollback();

    // Kirim response error
    send_json_response(500, ['success' => false, 'message' => $e->getMessage()]);
}

// Tutup koneksi
$conn->close();
?>
