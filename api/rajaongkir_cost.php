<?php
// api/rajaongkir_cost.php
header('Content-Type: application/json');
require_once 'config.php';

// Membaca input JSON atau method POST
$input = json_decode(file_get_contents('php://input'), true);

$origin = isset($input['origin']) ? $input['origin'] : (isset($_POST['origin']) ? $_POST['origin'] : '');
$destination = isset($input['destination']) ? $input['destination'] : (isset($_POST['destination']) ? $_POST['destination'] : '');
$weight = isset($input['weight']) ? $input['weight'] : (isset($_POST['weight']) ? $_POST['weight'] : '');
$courier = isset($input['courier']) ? $input['courier'] : (isset($_POST['courier']) ? $_POST['courier'] : '');

// Validasi
if (empty($origin) || empty($destination) || empty($weight) || empty($courier)) {
    echo json_encode([
        "rajaongkir" => [
            "status" => ["code" => 400, "description" => "Lengkapi data origin, destination, weight, courier"]
        ]
    ]);
    exit;
}

$postFields = http_build_query([
    'origin' => $origin,
    'destination' => $destination,
    'weight' => $weight,
    'courier' => $courier
]);

$response = callRajaOngkir('calculate/domestic-cost', 'POST', $postFields);
echo $response;
