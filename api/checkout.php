<?php
// api/checkout.php
header('Content-Type: application/json');
require_once 'config.php';

// --- CORS & Rate Limiting ---
addCorsHeaders();
checkRateLimit('checkout', 5, 60); // Maks 5 kali checkout per IP per menit

// ============================================================
//  SERVER-SIDE PRODUCT CATALOG
//  CRITICAL FIX: Harga dan berat ditetapkan di SERVER, bukan
//  diambil dari client. Client hanya mengirim ID produk & qty.
// ============================================================
const PRODUCT_CATALOG = [
    'PRD-1KG' => [
        'name'   => 'Tepung Mocafie Serbaguna 1kg',
        'price'  => 2000,    // TEST PRICE - Ubah kembali ke 25000 setelah testing selesai!
        'weight' => 1000,    // gram
    ],
    // Tambahkan produk baru di sini jika ada
    // 'PRD-500G' => ['name' => '...', 'price' => 14000, 'weight' => 500],
];

// --- Baca & validasi payload JSON ---
$input_raw = file_get_contents('php://input');
$input     = json_decode($input_raw, true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload JSON tidak valid']);
    exit;
}

// ============================================================
//  SANITASI INPUT CUSTOMER
// ============================================================
$name         = htmlspecialchars(strip_tags(trim($input['name']         ?? 'Customer')), ENT_QUOTES, 'UTF-8');
$phone        = preg_replace('/[^0-9+\-\s]/', '', trim($input['phone']  ?? ''));
$email        = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: 'no-email@example.com';
$destText     = htmlspecialchars(strip_tags(trim($input['destination']  ?? '')), ENT_QUOTES, 'UTF-8');
$detailAddress= htmlspecialchars(strip_tags(trim($input['address']      ?? '')), ENT_QUOTES, 'UTF-8');
$courierName  = htmlspecialchars(strip_tags(trim($input['courierName']  ?? 'JNE')), ENT_QUOTES, 'UTF-8');

// Ambil shipping cost dari input (ini aman karena harga produk dari catalog server)
$shippingCost = max(0, (int)($input['shippingCost'] ?? 0));

// Validasi minimal
if (empty($name) || $name === 'Customer') $name = 'Customer';
if (strlen($name) > 100) $name = substr($name, 0, 100);
if (strlen($detailAddress) > 500) $detailAddress = substr($detailAddress, 0, 500);

// ============================================================
//  BUILD ORDER DARI SERVER-SIDE CATALOG
//  Abaikan harga dari client, gunakan harga dari PRODUCT_CATALOG
// ============================================================
$cartItems   = isset($input['cartItems']) && is_array($input['cartItems']) ? $input['cartItems'] : [];
$orderId     = 'MCF-' . time() . '-' . rand(100, 999);
$grossAmount = 0;
$itemDetails = [];

if (empty($cartItems)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Keranjang belanja kosong']);
    exit;
}

foreach ($cartItems as $item) {
    $itemId = htmlspecialchars(strip_tags(trim($item['id'] ?? '')), ENT_QUOTES, 'UTF-8');

    // Pastikan produk dikenal oleh server
    if (!isset(PRODUCT_CATALOG[$itemId])) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Produk tidak dikenal: ' . $itemId,
        ]);
        exit;
    }

    $product = PRODUCT_CATALOG[$itemId];
    $price   = $product['price'];                    // ← Harga dari SERVER
    $qty     = max(1, min(100, (int)($item['qty'] ?? 1))); // Batasi qty 1–100
    $grossAmount += ($price * $qty);

    $itemDetails[] = [
        'id'       => $itemId,
        'price'    => $price,
        'quantity' => $qty,
        'name'     => substr($product['name'], 0, 50),
    ];
}

// Tambahkan ongkir sebagai item Midtrans
if ($shippingCost > 0) {
    $grossAmount   += $shippingCost;
    $itemDetails[] = [
        'id'       => 'SHIPPING',
        'price'    => $shippingCost,
        'quantity' => 1,
        'name'     => 'Ongkos Kirim (' . substr($courierName, 0, 30) . ')',
    ];
}

if ($grossAmount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Total pembayaran tidak valid']);
    exit;
}

// ============================================================
//  PANGGIL API MIDTRANS SNAP
// ============================================================
$midtrans_url = MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$transaction_data = [
    'transaction_details' => [
        'order_id'     => $orderId,
        'gross_amount' => $grossAmount,
    ],
    'item_details'     => $itemDetails,
    'customer_details' => [
        'first_name' => $name,
        'email'      => $email,
        'phone'      => $phone,
        'shipping_address' => [
            'first_name'   => $name,
            'phone'        => $phone,
            'address'      => $detailAddress,
            'city'         => $destText,
            'country_code' => 'IDN',
        ],
    ],
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $midtrans_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($transaction_data),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$result    = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => 'Gagal terhubung ke Midtrans']);
    exit;
}

$response = json_decode($result, true);

if ($httpCode === 201 || isset($response['token'])) {
    echo json_encode([
        'status'       => 'success',
        'token'        => $response['token'],
        'redirect_url' => $response['redirect_url'] ?? '',
        'order_id'     => $orderId,
    ]);
} else {
    // Jangan ekspos detail error Midtrans ke client di production
    http_response_code(502);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal membuat transaksi. Silakan coba lagi.',
        // Hanya tampilkan detail di sandbox/development
        'detail'  => MIDTRANS_IS_PRODUCTION ? null : ($response['error_messages'] ?? $response),
    ]);
}
