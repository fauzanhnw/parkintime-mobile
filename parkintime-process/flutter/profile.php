<?php
require 'db_connection.php'; // file koneksi ke database

$id_akun = $_GET['id_akun'];

$query = "SELECT * FROM profil WHERE id_akun = '$id_akun'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if ($data) {
    echo json_encode([
        'success' => true,
        'nama_lengkap' => $data['nama_lengkap']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Profil tidak ditemukan'
    ]);
}
?>
