<?php

header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $noreg = strtoupper(str_replace(' ', '', $_POST["noreg"]));
            $bbn = "0";
            $norangka = $_POST["norangka"];
           

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
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/x-www-form-urlencoded"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to contact Bapenda server']);
        exit;
    }

    $data = json_decode($response, true);

   if ($data && $data[0] === "ok" && $data[1] === "ok") {
    $info = $data[2][0];

    $vehicleData = [
        'no_kendaraan' => strtoupper($info[4] . " " . $info[5] . " " . $info[6]),
        'pemilik' => $info[7] ?? '-',
        'merek' => $info[20] ?? '-',
        'tipe' => $info[21] ?? '-',
        'kategori' => $info[18] ?? '-',
        'warna' => $info[14] ?? '-',
        'tahun' => $info[22] ?? '-',
        'kapasitas' => $info[23] ?? '-',
        'energi' => $info[16] ?? '-',
        'warna_plat' => $info[15] ?? '-',
    ];

    echo json_encode([
        'status' => 'success',
        'data' => $vehicleData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data not found'
    ]);
}

}
