<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require 'db_connection.php';

// Ambil ID Akun dari parameter
$id_akun = isset($_GET['id_akun']) ? $conn->real_escape_string($_GET['id_akun']) : null;

file_put_contents("debug_log_getcar.txt", print_r($_GET, true), FILE_APPEND);
file_put_contents("debug_log_getcar.txt", "ID Akun: $id_akun\n", FILE_APPEND);

if (!$id_akun) {
    echo json_encode([
        'status' => false,
        'message' => 'Parameter id_akun tidak tersedia'
    ]);
    exit();
}

// Query ambil data kendaraan berdasarkan id_akun
$sql = "SELECT 
            id,
            no_kendaraan,
            pemilik,
            merek,
            tipe,
            kategori,
            warna,
            tahun,
            kapasitas,
            energi,
            warna_plat
        FROM kendaraan
        WHERE id_akun = '$id_akun'";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        'status' => true,
        'data' => $data
    ]);
} else {
    echo json_encode([
        'status' => true,
        'data' => [] // Kosong jika tidak ada data
    ]);
}


$conn->close();
?>
