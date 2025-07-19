<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Identitas Kendaraan</title>
    <link rel="shortcut icon" href="avanza-gray-_1__optimized1-removebg-preview.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #e0f7fa, #ffffff);
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<div class="bg-white p-6 md:p-10 rounded-3xl shadow-2xl w-full max-w-6xl space-y-8">
    <h1 class="text-2xl md:text-3xl font-bold text-center text-blue-700">Cek Info Identitas Kendaraan</h1>

    <div class="flex flex-col md:flex-row gap-10">
        <!-- FORM KIRI -->
        <form method="post" class="flex-1 space-y-8">
            <!-- Input Noreg dengan Plat Nomor -->
            <div class="flex justify-center">
                <div class="relative w-full max-w-md">
                    <img src="plat nomor putih.png" alt="Plat Nomor" class="w-full rounded-2xl shadow">
                    <input 
                        type="text" 
                        name="noreg" 
                        id="noreg"
                        required
                        maxlength="9"
                        placeholder="BP1234YY"
                        class="absolute inset-0 w-full h-full bg-transparent text-center text-5xl md:text-6xl font-bold tracking-widest text-black uppercase focus:outline-none"
                        style="font-family: 'Roboto', sans-serif;"
                        oninput="formatNoreg(this)">
                </div>
            </div>

            <!-- Input Nomor Rangka -->
            <div>
                <label class="block mb-1 font-semibold text-gray-700">4 Digit Terakhir No Rangka</label>
                <input 
                    type="text" 
                    name="norangka" 
                    required 
                    maxlength="4" 
                    placeholder="Contoh: 1234"
                    class="w-full px-4 py-3 border rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent text-base md:text-lg">
            </div>

            <button type="submit"
                class="w-full py-3 px-4 bg-blue-600 text-white rounded-2xl font-semibold hover:bg-blue-700 transition shadow-md text-base md:text-lg">
                🔎 Cek Data
            </button>
        </form>

        <!-- HASIL KANAN -->
        <div class="flex-1">
            <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                // Validasi input lebih ketat
                $noreg = strtoupper(preg_replace("/[^A-Z0-9]/", "", $_POST["noreg"] ?? ''));
                $norangka = preg_replace("/[^0-9]/", "", $_POST["norangka"] ?? '');
                $bbn = "0";

                if (strlen($noreg) < 3 || strlen($norangka) !== 4) {
                    echo "<div class='text-red-500 font-semibold text-center'>❌ Format input tidak valid.</div>";
                } else {
                    $postData = http_build_query([
                        'noreg' => $noreg,
                        'bbn' => $bbn,
                        'norangka' => $norangka
                    ]);

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.bapenda.kepriprov.go.id/infopajaknorangkaweb.php");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

                    $response = curl_exec($ch);

                    // Tangani error jika request gagal
                    if ($response === false) {
                        echo "<div class='text-red-500 font-semibold text-center'>❌ Error cURL: " . curl_error($ch) . "</div>";
                    }

                    curl_close($ch);

                    $data = json_decode($response, true);

                    if ($data && $data[0] === "ok" && $data[1] === "ok") {
                        $info = $data[2][0];

                        $nopol = trim(strtoupper($info[4] . " " . $info[5] . " " . $info[6]));
                        $pemilik = $info[7] ?? '-';
                        $merk = $info[20] ?? '-';
                        $tipe = $info[21] ?? '-';
                        $kategori = $info[18] ?? '-';
                        $warna = $info[14] ?? '-';
                        $tahun = $info[22] ?? '-';
                        $silinder = $info[23] ?? '-';
                        $bahan_bakar = $info[16] ?? '-';
                        $plat = $info[15] ?? '-';

                        echo "<div class='bg-blue-50 p-6 rounded-2xl shadow-inner h-full'>";
                        echo "<h2 class='text-xl md:text-2xl font-bold text-blue-700 mb-6 text-center'>Hasil Data Kendaraan</h2>";
                        echo "<div class='space-y-4 text-gray-700 text-base md:text-lg'>";
                        
                        echo "<div class='flex justify-between'><span class='font-semibold'>Nomor Registrasi:</span><span class='text-right'>$nopol</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Nama Pemilik:</span><span class='text-right'>$pemilik</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Merek:</span><span class='text-right'>$merk</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Tipe:</span><span class='text-right'>$tipe</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Kategori:</span><span class='text-right'>$kategori</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Warna:</span><span class='text-right'>$warna</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Tahun Pembuatan:</span><span class='text-right'>$tahun</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Kapasitas Silinder:</span><span class='text-right'>$silinder CC</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Sumber Energi:</span><span class='text-right'>$bahan_bakar</span></div>";
                        echo "<div class='flex justify-between'><span class='font-semibold'>Warna Plat Nomor:</span><span class='text-right'>$plat</span></div>";

                        echo "</div></div>";
                    } else {
                        echo "<div class='text-red-500 font-semibold text-center'>⚠️ Data tidak ditemukan atau format tidak sesuai.</div>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>