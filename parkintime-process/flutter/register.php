<?php
header('Content-Type: application/json'); // Pastikan respons dalam format JSON

// Koneksi ke database
require 'db_connection.php';

// Aktifkan transaksi untuk menjaga konsistensi data
$conn->begin_transaction();

try {
    // Ambil data dari form
    $nama_lengkap = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = "user"; // Default role sebagai 'user'

    // Simpan ke tabel akun terlebih dahulu dengan role default
    $sql_akun = "INSERT INTO akun (email, password, role) VALUES (?, ?, ?)";
    $stmt_akun = $conn->prepare($sql_akun);
    $stmt_akun->bind_param("sss", $email, $password, $role);

    if (!$stmt_akun->execute()) {
        throw new Exception("Error akun: " . $stmt_akun->error);
    }

    $akun_id = $stmt_akun->insert_id; // Ambil ID yang baru dibuat dari akun

    // Simpan ke tabel profil dengan referensi email dan id_akun
    $sql_profil = "INSERT INTO profil (id_akun, email, nama_lengkap) VALUES (?, ?, ?)";
    $stmt_profil = $conn->prepare($sql_profil);
    $stmt_profil->bind_param("iss", $akun_id, $email, $nama_lengkap);

    if (!$stmt_profil->execute()) {
        throw new Exception("Error profil: " . $stmt_profil->error);
    }

    // Jika semua berhasil, commit transaksi
    $conn->commit();

    // Kirimkan respons JSON
    echo json_encode(["success" => true, "message" => "Register successful"]);
} catch (Exception $e) {
    $conn->rollback(); // Rollback jika ada kesalahan
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

// Tutup koneksi
$stmt_akun->close();
$stmt_profil->close();
$conn->close();
?>