<?php
/**
 * API Endpoint untuk mendapatkan Service Fee.
 * Mengembalikan nominal service fee dalam format JSON.
 */

// Mengatur header respons menjadi JSON, agar client tahu cara mem-parsing data.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Opsional: untuk mengizinkan akses dari domain mana pun.

// --- ATUR NOMINAL SERVICE FEE DI SINI ---
// Anda bisa mengubah nilai ini atau mengambilnya dari database sesuai kebutuhan.
$service_fee = 400; 

// Menyiapkan array data yang akan diubah menjadi JSON.
// Strukturnya harus cocok dengan yang diharapkan oleh aplikasi Flutter.
$response = [
    'success' => true,
    'message' => 'Service fee retrieved successfully.',
    'data' => [
        'service_fee' => $service_fee
    ]
];

// Mengubah array PHP menjadi format string JSON dan mengirimkannya sebagai respons.
echo json_encode($response);

// Menghentikan eksekusi script setelah respons dikirim.
exit();

?>