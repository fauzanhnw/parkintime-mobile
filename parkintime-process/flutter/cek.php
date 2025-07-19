<?php
// Menampilkan header JSON
header('Content-Type: application/json');

// Mendapatkan informasi server
$server_status = [
    'server_name' => $_SERVER['SERVER_NAME'],
    'server_ip' => gethostbyname($_SERVER['SERVER_NAME']),
    'server_software' => $_SERVER['SERVER_SOFTWARE'],
    'php_version' => phpversion(),
    'memory_usage' => memory_get_usage(),
    'disk_free_space' => disk_free_space("/"),
    'disk_total_space' => disk_total_space("/"),
];

// Mengecek apakah server dapat terhubung ke internet
$internet_status = @fsockopen("www.google.com", 80) ? 'Online' : 'Offline';
$server_status['internet_connection'] = $internet_status;

// Mengembalikan respon JSON
echo json_encode($server_status, JSON_PRETTY_PRINT);
?>