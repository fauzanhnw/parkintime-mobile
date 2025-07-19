<?php
header('Content-Type: application/json');
file_put_contents("debug_log_spot.txt", print_r($_GET, true), FILE_APPEND);

include 'db_connection.php';

$id_lahan = $_GET['id_lahan'];

$query = "SELECT kode_slot, area, status FROM slot_parkir WHERE id_lahan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_lahan);
$stmt->execute();
$result = $stmt->get_result();

$slots = [];
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}

echo json_encode($slots);