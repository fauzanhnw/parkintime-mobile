<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");
require 'db_connection.php';

$id_akun = $_POST['id_akun'];
$response = ['success' => false, 'data' => []];

$query = "
    SELECT 
        t.id AS ticket_id,
        t.status,
        t.waktu_masuk,
        t.waktu_keluar,
        t.biaya_total,
        s.kode_slot,
        s.area,
        l.nama_lokasi,
        l.jenis
    FROM tiket t
    JOIN slot s ON t.id_slot = s.id
    JOIN lahan l ON s.id_lahan = l.id
    WHERE t.id_akun = ?
    ORDER BY t.waktu_masuk DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_akun);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $response['data'][] = $row;
}

$response['success'] = true;
echo json_encode($response);
?>
