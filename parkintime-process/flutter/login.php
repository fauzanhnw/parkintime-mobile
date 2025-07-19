<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'db_connection.php';

// Ambil dan bersihkan input
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email dan password harus diisi'
    ]);
    exit;
}

// Cegah SQL Injection dengan prepared statement
$stmt = $conn->prepare("SELECT id, email, password FROM akun WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $id_akun = $user['id'];

    // Ambil nama lengkap dari tabel profil
    $stmt2 = $conn->prepare("SELECT nama_lengkap FROM profil WHERE id_akun = ?");
    $stmt2->bind_param("i", $id_akun);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $profil = $result2->fetch_assoc();

    echo json_encode([
        'success' => true,
        'data' => [
            'id_akun' => $id_akun,
            'email' => $user['email'],
            'name' => $profil['nama_lengkap'] ?? 'User'
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Email atau password salah'
    ]);
}
?>
