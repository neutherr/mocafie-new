<?php
// api/rajaongkir_cost.php
header('Content-Type: application/json');
require_once 'config.php';
addCorsHeaders();
checkRateLimit('ongkir_cost', 30, 60); // 30 request per menit per IP

// Membaca input JSON atau fallback ke POST
$input = json_decode(file_get_contents('php://input'), true);

// Sanitasi & ambil parameter
$origin      = isset($input['origin'])      ? (int)$input['origin']      : (int)($_POST['origin']      ?? 0);
$destination = isset($input['destination']) ? (int)$input['destination'] : (int)($_POST['destination'] ?? 0);
$weight      = isset($input['weight'])      ? (int)$input['weight']      : (int)($_POST['weight']      ?? 0);
$courier     = isset($input['courier'])     ? preg_replace('/[^a-z]/', '', strtolower(trim($input['courier']     ?? ''))) 
                                            : preg_replace('/[^a-z]/', '', strtolower(trim($_POST['courier'] ?? '')));

// Validasi daftar kurir yang diizinkan
$allowedCouriers = ['jne', 'pos', 'tiki', 'jnt', 'sicepat', 'anteraja', 'ninja', 'lion', 'wahana'];
if (!in_array($courier, $allowedCouriers, true)) {
    http_response_code(400);
    echo json_encode([
        'rajaongkir' => [
            'status' => ['code' => 400, 'description' => 'Kurir tidak valid'],
        ],
    ]);
    exit;
}

// Validasi field wajib
if ($origin <= 0 || $destination <= 0 || $weight <= 0) {
    http_response_code(400);
    echo json_encode([
        'rajaongkir' => [
            'status' => ['code' => 400, 'description' => 'Lengkapi data origin, destination, dan weight'],
        ],
    ]);
    exit;
}

// Batasi berat maksimum (contoh: 70 kg = 70.000 gram)
if ($weight > 70000) {
    http_response_code(400);
    echo json_encode([
        'rajaongkir' => [
            'status' => ['code' => 400, 'description' => 'Berat melebihi batas maksimum (70 kg)'],
        ],
    ]);
    exit;
}

$postFields = http_build_query([
    'origin'      => $origin,
    'destination' => $destination,
    'weight'      => $weight,
    'courier'     => $courier,
]);

$response = callRajaOngkir('calculate/domestic-cost', 'POST', $postFields);
echo $response;
