<?php
// koneksi.php
// File ini digunakan untuk menyambungkan ke database MySQL

$host     = "localhost";   // Nama host database
$username = " ";        // Username database
$password = " ";            // Password database
$database = " "; // Ganti dengan nama database Anda

// Membuat koneksi ke MySQL
$koneksi = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($koneksi->connect_error) {
    // Menampilkan pesan error saat pengembangan
    echo "<div style='
        font-family: sans-serif;
        background-color: #ffdddd;
        color: #a94442;
        padding: 15px;
        margin: 20px;
        border-left: 5px solid #f44336;
        border-radius: 4px;
    '>
        <strong>Koneksi Gagal!</strong><br>
        Error: " . htmlspecialchars($koneksi->connect_error) . "
    </div>";
    die(); // Hentikan eksekusi
}

// Jika berhasil
echo "<div style='
    font-family: sans-serif;
    background-color: #ddffdd;
    color: #3c763d;
    padding: 15px;
    margin: 20px;
    border-left: 5px solid #4CAF50;
    border-radius: 4px;
'>
    <strong>Koneksi Berhasil!</strong> Terhubung ke database 
</div>";
?>
