<?php
header('Content-Type: application/json');
require_once 'config.php';

// Ambil input pencarian dari frontend (contoh: "sleman" atau "cipete")
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    echo json_encode(['status' => 'error', 'message' => 'Keyword pencarian kosong']);
    exit;
}

// Endpoint Komerce untuk mencari destinasi domestik (bisa nama kecamatan / kota)
// Diset limit 10 agar tidak terlalu banyak opsi kembaran yang membingungkan.
$endpoint = "destination/domestic-destination?search=" . urlencode($search) . "&limit=10&offset=0";

// Panggil API (callRajaOngkir didefinisikan di config.php)
$response = callRajaOngkir($endpoint);

echo $response;
?>
