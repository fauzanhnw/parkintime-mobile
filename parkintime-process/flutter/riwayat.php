<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set('Asia/Jakarta');

require 'db_connection.php';

// Ambil ID akun dari POST
$id_akun = $_POST['id_akun'] ?? null;

$response = [
    'success' => false,
    'data' => []
];

if ($id_akun) {
    $query = "
        SELECT 
            t.id AS tiket_id,
            t.order_id,
            t.status,
            t.status_pembayaran,
            t.waktu_masuk,
            t.waktu_keluar,
            t.biaya_total,
            s.kode_slot,
            l.nama_lokasi,
            l.jenis
        FROM tiket t
        JOIN slot_parkir s ON t.id_slot = s.id
        JOIN lahan_parkir l ON s.id_lahan = l.id
        WHERE t.id_akun = ?
        ORDER BY t.waktu_masuk DESC
    ";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $id_akun);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Cek dan update status jika perlu
            $currentTime = new DateTime();
            if (
                $row['status'] === 'valid' &&
                $row['waktu_keluar'] &&
                $row['waktu_keluar'] < $currentTime->format('Y-m-d H:i:s')
            ) {
                // Update status ke "completed"
                $updateQuery = "UPDATE tiket SET status = 'completed' WHERE id = ?";
                if ($updateStmt = $conn->prepare($updateQuery)) {
                    $updateStmt->bind_param("i", $row['tiket_id']);
                    $updateStmt->execute();
                    $updateStmt->close();

                    // Perbarui data yang dikembalikan juga
                    $row['status'] = 'completed';
                }
            }

            $response['data'][] = $row;
        }

        $response['success'] = true;
        $stmt->close();
    }
}

echo json_encode($response);
?>
