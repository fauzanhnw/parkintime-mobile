<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

require 'db_connection.php';

$data = $_POST;

// Daftar field yang wajib diisi
$required_fields = [
    'id_akun', 'no_kendaraan', 'pemilik', 'merek', 'tipe',
    'kategori', 'warna', 'tahun', 'kapasitas', 'energi', 'warna_plat'
];

// Validasi input
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        response('error', "Field $field is missing.");
    }
}

// Cek apakah kendaraan sudah terdaftar untuk akun ini
$check = $conn->prepare("SELECT id FROM kendaraan WHERE no_kendaraan = ? AND id_akun = ?");
$check->bind_param("ss", $data['no_kendaraan'], $data['id_akun']);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    response('error', 'Vehicle already exists for this account.');
}

// Persiapkan query insert
$stmt = $conn->prepare("
    INSERT INTO kendaraan (
        id_akun, no_kendaraan, pemilik, merek, tipe,
        kategori, warna, tahun, kapasitas, energi, warna_plat
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    response('error', 'Prepare failed: ' . $conn->error);
}

// Bind dan eksekusi
$stmt->bind_param(
    "sssssssssss",
    $data['id_akun'],
    $data['no_kendaraan'],
    $data['pemilik'],
    $data['merek'],
    $data['tipe'],
    $data['kategori'],
    $data['warna'],
    $data['tahun'],
    $data['kapasitas'],
    $data['energi'],
    $data['warna_plat']
);

if ($stmt->execute()) {
    response('success', 'Vehicle added successfully.');
} else {
    response('error', 'Insert failed: ' . $stmt->error);
}

// Fungsi response JSON
function response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
?>
