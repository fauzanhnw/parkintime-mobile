<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

require 'db_connection.php';

$email = $_POST['email'] ?? '';
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

$response = ['success' => false, 'message' => ''];

if (empty($email) || empty($current_password) || empty($new_password)) {
    $response['message'] = 'Email, current password, and new password are required';
    echo json_encode($response);
    exit();
}

// Query untuk mengambil password yang sudah di-hash
$query = "SELECT password FROM akun WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Memverifikasi password yang diterima dengan password yang tersimpan (password_hash)
    if (password_verify($current_password, $row['password'])) {
        // Hash password baru
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password dengan password yang baru
        $update = "UPDATE akun SET password = ? WHERE email = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("ss", $new_password_hashed, $email);
        if ($stmt2->execute()) {
            $response['success'] = true;
            $response['message'] = 'Password updated successfully';
        } else {
            $response['message'] = 'Failed to update password';
        }
    } else {
        $response['message'] = 'Incorrect current password';
    }
} else {
    $response['message'] = 'User not found';
}

echo json_encode($response);
?>
