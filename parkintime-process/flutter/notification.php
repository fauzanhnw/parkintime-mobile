<?php
// Sertakan file koneksi database dan library Midtrans
require 'db_connection.php';
require_once dirname(__FILE__) . '/midtrans-php-master/Midtrans.php';

// -----------------------------------------------------------------------------
// ⚙️ KONFIGURASI MIDTRANS
// -----------------------------------------------------------------------------
Midtrans\Config::$serverKey = ' '; // Ganti dengan Server Key Anda
Midtrans\Config::$isProduction = false; // Set true jika sudah di production

// -----------------------------------------------------------------------------
// 📥 MENERIMA NOTIFIKASI DARI MIDTRANS
// -----------------------------------------------------------------------------
// Ambil data JSON yang dikirim oleh Midtrans
$json_result = file_get_contents('php://input');
$notification = json_decode($json_result, true);

// Pastikan notifikasi tidak kosong
if (!$notification) {
    // Kirim respons error jika tidak ada data
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid notification data.']);
    exit();
}

// -----------------------------------------------------------------------------
// 🔒 VERIFIKASI SIGNATURE KEY (SANGAT PENTING!)
// -----------------------------------------------------------------------------
// Ambil data yang diperlukan untuk verifikasi
$order_id = $notification['order_id'];
$status_code = $notification['status_code'];
$gross_amount = $notification['gross_amount'];
$server_key = Midtrans\Config::$serverKey;

// Buat signature key di sisi server Anda
$my_signature_key = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

// Bandingkan signature key dari Midtrans dengan yang Anda buat
if ($notification['signature_key'] !== $my_signature_key) {
    // Kirim respons error jika signature tidak cocok
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Invalid signature.']);
    exit();
}

// -----------------------------------------------------------------------------
// ✅ PROSES STATUS TRANSAKSI
// -----------------------------------------------------------------------------
$transaction_status = $notification['transaction_status'];
$fraud_status = !empty($notification['fraud_status']) ? $notification['fraud_status'] : '';

// Gunakan prepared statement untuk keamanan
$stmt = $conn->prepare("UPDATE tiket SET status_pembayaran = ?, status = ? WHERE order_id = ?");

if ($transaction_status == 'capture') {
    // Untuk transaksi kartu kredit yang berhasil
    if ($fraud_status == 'accept') {
        // Status pembayaran: lunas, Status tiket: valid
        $payment_status = 'settlement';
        $ticket_status = 'valid';
        $stmt->bind_param("sss", $payment_status, $ticket_status, $order_id);
        $stmt->execute();
        
        // BARU: Tambahkan logika untuk update status slot parkir
        updateParkingSlotStatus($conn, $order_id);
    }
} else if ($transaction_status == 'settlement') {
    // Untuk transaksi non-kartu kredit yang berhasil
    // Status pembayaran: lunas, Status tiket: valid
    $payment_status = 'settlement';
    $ticket_status = 'valid';
    $stmt->bind_param("sss", $payment_status, $ticket_status, $order_id);
    $stmt->execute();

    // BARU: Tambahkan logika untuk update status slot parkir
    updateParkingSlotStatus($conn, $order_id);

} else if ($transaction_status == 'pending') {
    // Transaksi masih menunggu pembayaran
    // Status pembayaran: pending, Status tiket: pending
    $payment_status = 'pending';
    $ticket_status = 'pending';
    $stmt->bind_param("sss", $payment_status, $ticket_status, $order_id);
    $stmt->execute();
} else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
    // Transaksi gagal, kadaluarsa, atau dibatalkan
    // Status pembayaran: failed, Status tiket: expired
    $payment_status = 'failed';
    $ticket_status = 'expired';
    $stmt->bind_param("sss", $payment_status, $ticket_status, $order_id);
    $stmt->execute();
}

/**
 * BARU: Fungsi untuk mengubah status slot parkir menjadi 'booked'
 *
 * @param mysqli $conn Koneksi database
 * @param string $order_id ID Pesanan dari Midtrans
 */
function updateParkingSlotStatus($conn, $order_id) {
    // 1. Dapatkan id_slot dari tabel tiket berdasarkan order_id
    $stmt_get_slot = $conn->prepare("SELECT id_slot FROM tiket WHERE order_id = ?");
    $stmt_get_slot->bind_param("s", $order_id);
    $stmt_get_slot->execute();
    $result = $stmt_get_slot->get_result();
    
    if ($result->num_rows > 0) {
        $tiket = $result->fetch_assoc();
        $id_slot = $tiket['id_slot'];

        // 2. Jika id_slot ada, update status di tabel slot_parkir
        if ($id_slot) {
            $new_status = 'booked';
            $stmt_update_slot = $conn->prepare("UPDATE slot_parkir SET status = ? WHERE id = ?");
            $stmt_update_slot->bind_param("si", $new_status, $id_slot);
            $stmt_update_slot->execute();
            $stmt_update_slot->close();
        }
    }
    $stmt_get_slot->close();
}


$stmt->close();
$conn->close();

// -----------------------------------------------------------------------------
// 💡 KIRIM RESPON 200 OK
// -----------------------------------------------------------------------------
// Kirim respons HTTP 200 OK untuk memberitahu Midtrans bahwa notifikasi telah diterima
http_response_code(200);
echo json_encode(['status' => true, 'message' => 'Notification processed.']);

?>