<?php
// api/rajaongkir_destination.php
header('Content-Type: application/json');
require_once 'config.php';
addCorsHeaders();
checkRateLimit('ongkir_dest', 30, 60); // 30 request per menit per IP

// Sanitasi input pencarian
$search = htmlspecialchars(strip_tags(trim($_GET['search'] ?? '')), ENT_QUOTES, 'UTF-8');

if (empty($search)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Keyword pencarian kosong']);
    exit;
}

// Batasi panjang keyword (hindari pencarian yang terlalu panjang)
if (strlen($search) < 3) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Keyword minimal 3 karakter']);
    exit;
}

if (strlen($search) > 100) {
    $search = substr($search, 0, 100);
}

// Panggil API RajaOngkir / Komerce
$endpoint = 'destination/domestic-destination?search=' . urlencode($search) . '&limit=10&offset=0';
$response = callRajaOngkir($endpoint);
echo $response;
