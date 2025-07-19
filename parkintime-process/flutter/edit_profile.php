<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/x-www-form-urlencoded; charset=UTF-8");

require 'db_connection.php';

$email = $_POST['email'];
$newName = $_POST['name'];

$response = [];

if ($email && $newName) {
    $query = "UPDATE profil SET name='$newName' WHERE email='$email'";
    if (mysqli_query($conn, $query)) {
        $response['success'] = true;
        $response['message'] = "Profil berhasil diperbarui";
    } else {
        $response['success'] = false;
        $response['message'] = "Gagal memperbarui profil";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Data tidak lengkap";
}

echo json_encode($response);
?>
