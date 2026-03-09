<?php
// api/notification.php
// Webhook / Notifikasi dari Midtrans ketika pembayaran berhasil.

header('Content-Type: application/json');
require_once 'config.php';
addCorsHeaders();

$serverKey  = MIDTRANS_SERVER_KEY;
$rawPayload = file_get_contents('php://input');
$notification = json_decode($rawPayload, true);

if (!$notification || !is_array($notification)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format notifikasi tidak valid']);
    exit;
}

// Data dari notifikasi Midtrans
$orderId           = isset($notification['order_id'])           ? htmlspecialchars($notification['order_id'],           ENT_QUOTES, 'UTF-8') : '';
$statusCode        = isset($notification['status_code'])        ? htmlspecialchars($notification['status_code'],        ENT_QUOTES, 'UTF-8') : '';
$grossAmount       = isset($notification['gross_amount'])       ? htmlspecialchars($notification['gross_amount'],       ENT_QUOTES, 'UTF-8') : '';
$transactionStatus = isset($notification['transaction_status']) ? htmlspecialchars($notification['transaction_status'], ENT_QUOTES, 'UTF-8') : '';
$signatureKey      = isset($notification['signature_key'])      ? $notification['signature_key'] : '';

// Validasi field wajib
if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($signatureKey)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Field notifikasi tidak lengkap']);
    exit;
}

// ============================================================
//  VERIFIKASI SIGNATURE (Otentikasi Notifikasi)
//  Rumus: SHA512(order_id + status_code + gross_amount + server_key)
// ============================================================
$calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if (!hash_equals($calculatedSignature, $signatureKey)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Signature tidak valid']);
    exit;
}

// ============================================================
//  PROSES STATUS PEMBAYARAN
// ============================================================
if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
    // PEMBAYARAN SUKSES / LUNAS
    // FIX KEAMANAN: Gunakan error_log() (masuk server log, tidak bisa diakses publik)
    // Tidak menulis ke payment_success_log.txt di webroot
    $logEntry = '[MOCAFIE PAYMENT] ' . date('Y-m-d H:i:s')
        . ' | STATUS: LUNAS'
        . ' | ORDER: ' . $orderId
        . ' | NOMINAL: Rp ' . $grossAmount;
    error_log($logEntry);

    // TODO: Tambahkan kode untuk:
    // a. Simpan status "LUNAS" ke Database MySQL
    // b. Kurangi stok produk di database
    // c. Kirim email/WhatsApp konfirmasi ke Admin & Pembeli

    echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil diproses']);

} elseif ($transactionStatus === 'pending') {
    echo json_encode(['status' => 'pending', 'message' => 'Menunggu pembayaran customer']);

} elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
    // TODO: Kembalikan stok produk jika sempat dikurangi
    echo json_encode(['status' => 'failed', 'message' => 'Pembayaran gagal atau kadaluarsa']);

} else {
    echo json_encode(['status' => 'unknown', 'message' => 'Status tidak dikenali: ' . $transactionStatus]);
}
