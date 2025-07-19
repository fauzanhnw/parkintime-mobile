<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require 'db_connection.php';

// --- QUERY YANG DIOPTIMALKAN ---
// Menggunakan LEFT JOIN dan GROUP BY untuk mendapatkan semua data dalam satu kali jalan.
// Ini jauh lebih efisien daripada query di dalam loop.
$sql = "
    SELECT 
        lp.id, 
        lp.nama_lokasi, 
        lp.alamat, 
        lp.jenis, 
        lp.foto, 
        lp.tarif_per_jam, 
        lp.status,
        lp.latitude,  -- Kolom baru yang kita tambahkan
        lp.longitude, -- Kolom baru yang kita tambahkan
        COUNT(sp.id_lahan) as kapasitas, 
        SUM(CASE WHEN sp.status IN ('occupied', 'booked') THEN 1 ELSE 0 END) as terisi
    FROM 
        lahan_parkir lp
    LEFT JOIN 
        slot_parkir sp ON lp.id = sp.id_lahan
    GROUP BY 
        lp.id
    ORDER BY 
        lp.created_at DESC
";

$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Karena data sudah lengkap dari satu query, kita tinggal masukkan ke array
        $data[] = [
            "id" => $row['id'],
            "nama_lokasi" => $row['nama_lokasi'],
            "alamat" => $row['alamat'],
            "jenis" => $row['jenis'],
            "foto" => $row['foto'],
            "tarif_per_jam" => $row['tarif_per_jam'],
            "status" => $row['status'],
            "latitude" => $row['latitude'],   // Data koordinat untuk Flutter
            "longitude" => $row['longitude'], // Data koordinat untuk Flutter
            "kapasitas" => (int)($row['kapasitas'] ?? 0),
            "terisi" => (int)($row['terisi'] ?? 0),
        ];
    }
    
    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Tidak ada data lahan parkir ditemukan.",
        "data" => [] // Selalu kirim array kosong agar tidak error di Flutter
    ]);
}

$conn->close();
?>