<?php
// api/checkout.php
header('Content-Type: application/json');
require_once 'config.php';

// Terima payload JSON dari frontend
$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
    exit;
}

$cartItems = isset($input['cartItems']) ? $input['cartItems'] : [];
$shippingCost = isset($input['shippingCost']) ? (int)$input['shippingCost'] : 0;
$courierName = isset($input['courierName']) ? $input['courierName'] : 'Kurir';

// Info Customer
$name = isset($input['name']) ? $input['name'] : 'Customer';
$phone = isset($input['phone']) ? $input['phone'] : '';
$email = isset($input['email']) ? $input['email'] : 'no-email@example.com';
$destText = isset($input['destination']) ? $input['destination'] : '';
$detailAddress = isset($input['address']) ? $input['address'] : '';

// 1. Susun order_id dan harga
$orderId = 'MCF-' . time() . '-' . rand(100, 999);
$grossAmount = 0;
$itemDetails = [];

foreach ($cartItems as $item) {
    $price = (int)$item['price'];
    $qty = (int)$item['qty'];
    $grossAmount += ($price * $qty);
    
    $itemDetails[] = [
        'id' => $item['id'],
        'price' => $price,
        'quantity' => $qty,
        'name' => substr($item['name'], 0, 50)
    ];
}

// Tambahkan ongkir sebagai salah satu "item" agar tercatat di rincian Midtrans
if ($shippingCost > 0) {
    $grossAmount += $shippingCost;
    $itemDetails[] = [
        'id' => 'SHIPPING',
        'price' => $shippingCost,
        'quantity' => 1,
        'name' => 'Ongkos Kirim (' . $courierName . ')'
    ];
}

if ($grossAmount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Total pembayaran tidak valid']);
    exit;
}

// 2. Siapkan Parameter Midtrans Snap
$midtrans_url = MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$transaction_data = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $grossAmount,
    ],
    'item_details' => $itemDetails,
    'customer_details' => [
        'first_name' => $name,
        'email' => $email,
        'phone' => $phone,
        'shipping_address' => [
            'first_name' => $name,
            'phone' => $phone,
            'address' => $detailAddress,
            'city' => $destText,
            'country_code' => 'IDN'
        ]
    ]
];

// 3. Panggil API Midtrans
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $midtrans_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($transaction_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
));

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $curlError]);
    exit;
}

$response = json_decode($result, true);

if ($httpCode == 201 || isset($response['token'])) {
    echo json_encode([
        'status' => 'success',
        'token' => $response['token'],
        'redirect_url' => $response['redirect_url'] ?? '',
        'order_id' => $orderId
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Midtrans Error',
        'raw' => $response
    ]);
}
?>
