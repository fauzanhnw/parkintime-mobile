<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

file_put_contents("debug_log_spot.txt", print_r($_GET, true), FILE_APPEND);

include 'db_connection.php';

$id_lahan = $_GET['id_lahan'];

$query = "SELECT kode_slot, status FROM slot_parkir WHERE id_lahan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_lahan);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];

while ($row = $result->fetch_assoc()) {
    // Ambil huruf dari awal kode_slot sebagai area
    if (preg_match('/^([A-Za-z]+)\d+$/', $row['kode_slot'], $matches)) {
        $row['area'] = "Area " . strtoupper($matches[1]); // Area = huruf depan
    } else {
        $row['area'] = "Unknown";
    }

    $slots[] = $row;
}

echo json_encode($slots);
