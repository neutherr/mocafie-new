<?php
// api/notification.php
// Script ini digunakan untuk menerima Webhook / Notifikasi dari Midtrans
// ketika pembeli berhasil melakukan pembayaran.

header('Content-Type: application/json');
require_once 'config.php';

// Midtrans Server Key (Diambil dari config.php ketika Anda sudah mengubahnya nanti)
$serverKey = MIDTRANS_SERVER_KEY;

// Mengambil raw body dari Midtrans
$rawPayload = file_get_contents('php://input');
$notification = json_decode($rawPayload, true);

if (!$notification) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Format notifikasi tidak valid"]);
    exit;
}

// Data dari notifikasi Midtrans
$orderId = isset($notification['order_id']) ? $notification['order_id'] : '';
$statusCode = isset($notification['status_code']) ? $notification['status_code'] : '';
$grossAmount = isset($notification['gross_amount']) ? $notification['gross_amount'] : '';
$transactionStatus = isset($notification['transaction_status']) ? $notification['transaction_status'] : '';
$signatureKey = isset($notification['signature_key']) ? $notification['signature_key'] : '';

// 1. Verifikasi Keaslian Notifikasi (Signature Key)
// Rumus Signature: SHA512(order_id + status_code + gross_amount + server_key)
$calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if ($signatureKey !== $calculatedSignature) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Signature Key tidak valid / Ditolak!"]);
    exit;
}

// 2. Pantau Status Pembayaran
if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
    // PEMBAYARAN SUKSES / LUNAS
    // TODO: Di sinilah Anda menambahkan kode untuk:
    // a. Menyimpan status "LUNAS" ke dalam Database MySQL (jika Anda pakai database)
    // b. Mengurangi stok produk di database
    // c. Mengirim email konfirmasi / WhatsApp otomatis ke Admin atau Pembeli
    
    // Contoh log pembayaran sukses ke file komputer Anda (hanya untuk testing)
    $logData = date("Y-m-d H:i:s") . " - PESANAN LUNAS: " . $orderId . " | Nominal: " . $grossAmount . "\n";
    file_put_contents('payment_success_log.txt', $logData, FILE_APPEND);

    echo json_encode(["status" => "success", "message" => "Pembayaran berhasil diproses"]);

} else if ($transactionStatus == 'pending') {
    // PEMBAYARAN TERTUNDA (Customer sudah checkout masuk Snap, tapi belum ke ATM/Transfer)
    echo json_encode(["status" => "pending", "message" => "Menunggu pembayaran customer"]);

} else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
    // PEMBAYARAN GAGAL / KADALUARSA
    // TODO: Kembalikan stok produk jika sempat dikurangi 
    echo json_encode(["status" => "failed", "message" => "Pembayaran gagal atau kadaluarsa"]);
} else {
    echo json_encode(["status" => "unknown", "message" => "Status tidak dikenali"]);
}
?>
