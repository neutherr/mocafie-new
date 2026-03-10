<?php
// api/notification.php
// Webhook handler: Midtrans memanggil URL ini saat status pembayaran berubah.
// Wajib diset di dashboard Midtrans → Settings → Configuration → Payment Notification URL

header('Content-Type: application/json');
require_once 'config.php';

$serverKey   = MIDTRANS_SERVER_KEY;
$adminEmail  = getenv('ADMIN_EMAIL')  ?: 'admin@mocafie.com';
$adminPhone  = getenv('ADMIN_PHONE')  ?: '';
$fonnteToken = getenv('FONNTE_TOKEN') ?: '';

// ------------------------------------------------------------
//  Baca payload dari Midtrans
// ------------------------------------------------------------
$rawPayload   = file_get_contents('php://input');
$notification = json_decode($rawPayload, true);

if (!$notification) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Payload tidak valid"]);
    exit;
}

$orderId           = $notification['order_id']           ?? '';
$statusCode        = $notification['status_code']        ?? '';
$grossAmount       = $notification['gross_amount']       ?? '';
$transactionStatus = $notification['transaction_status'] ?? '';
$fraudStatus       = $notification['fraud_status']       ?? '';
$paymentType       = $notification['payment_type']       ?? '';
$signatureKey      = $notification['signature_key']      ?? '';

// ---- Nama customer ----
$customerName  = $notification['customer_details']['first_name']   ?? 'Customer';
$customerEmail = $notification['customer_details']['email']        ?? '';
$customerPhone = $notification['customer_details']['phone']        ?? '';

// ------------------------------------------------------------
//  1. Verifikasi Signature Key (keamanan)
// ------------------------------------------------------------
$calculated = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
if ($signatureKey !== $calculated) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Signature tidak valid"]);
    exit;
}

// ------------------------------------------------------------
//  2. Tentukan status transaksi
// ------------------------------------------------------------
$isPaid = ($transactionStatus === 'capture'    && $fraudStatus === 'accept')
       || ($transactionStatus === 'settlement');

$isPending = $transactionStatus === 'pending';
$isFailed  = in_array($transactionStatus, ['deny', 'expire', 'cancel'], true);

// ------------------------------------------------------------
//  HELPER: Kirim WA via Fonnte
// ------------------------------------------------------------
function sendWhatsApp(string $token, string $target, string $message): bool {
    if (empty($token) || empty($target)) return false;
    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'target'  => $target,
            'message' => $message,
        ]),
        CURLOPT_HTTPHEADER => ["Authorization: $token"],
        CURLOPT_TIMEOUT    => 10,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $decoded = json_decode($res, true);
    return !empty($decoded['status']);
}

// ------------------------------------------------------------
//  HELPER: Kirim Email via PHP mail()
//  (Hostinger mendukung mail() standar)
// ------------------------------------------------------------
function sendEmail(string $to, string $subject, string $htmlBody, string $fromEmail = 'noreply@mocafie.com'): bool {
    if (empty($to)) return false;
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Mocafie <$fromEmail>\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    return (bool) mail($to, $subject, $htmlBody, $headers);
}

// ------------------------------------------------------------
//  HELPER: Format Rupiah
// ------------------------------------------------------------
function rupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// ------------------------------------------------------------
//  3. Proses berdasarkan status
// ------------------------------------------------------------
if ($isPaid) {

    $amount = rupiah((int) $grossAmount);

    // ── A. Email ke CUSTOMER ──────────────────────────────
    if (!empty($customerEmail)) {
        $subjCustomer = "✅ Pesanan Anda #{$orderId} Berhasil Dibayar — Mocafie";
        $bodyCustomer = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden'>
          <div style='background:#2D6A2B;padding:24px;text-align:center'>
            <h1 style='color:#fff;margin:0;font-size:22px'>🌿 Mocafie</h1>
            <p style='color:#c8e6c9;margin:4px 0 0'>Tepung Mocaf Premium</p>
          </div>
          <div style='padding:24px'>
            <p>Halo <strong>{$customerName}</strong>,</p>
            <p>Pembayaran Anda <strong>berhasil diterima</strong>! 🎉 Terima kasih sudah mempercayai Mocafie.</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0'>
              <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Nomor Pesanan</td><td style='padding:8px'>{$orderId}</td></tr>
              <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Status</td><td style='padding:8px;color:green'><strong>LUNAS ✅</strong></td></tr>
              <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Total Dibayar</td><td style='padding:8px'><strong>{$amount}</strong></td></tr>
              <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Metode Bayar</td><td style='padding:8px'>{$paymentType}</td></tr>
            </table>
            <p>Pesanan Anda segera kami proses dan akan dikirim dalam <strong>1–2 hari kerja</strong>.</p>
            <p style='color:#666;font-size:13px'>Pertanyaan? Hubungi kami via WhatsApp: <a href='https://wa.me/6285188789052'>0851-8878-9052</a></p>
          </div>
          <div style='background:#f5f5f5;padding:12px;text-align:center;font-size:12px;color:#999'>
            &copy; 2026 Mocafie Indonesia — mocafie.com
          </div>
        </div>";
        sendEmail($customerEmail, $subjCustomer, $bodyCustomer);
    }

    // ── B. WA ke CUSTOMER ─────────────────────────────────
    if (!empty($customerPhone) && !empty($fonnteToken)) {
        $custPhone = preg_replace('/[^0-9]/', '', $customerPhone);
        if (str_starts_with($custPhone, '0')) $custPhone = '62' . substr($custPhone, 1);
        $msgCustomer = "✅ *Pembayaran Berhasil — Mocafie*\n\n"
            . "Halo *{$customerName}*! Pembayaran Anda telah kami terima.\n\n"
            . "📦 *No. Pesanan:* {$orderId}\n"
            . "💰 *Total:* {$amount}\n"
            . "🏦 *Metode:* {$paymentType}\n\n"
            . "Pesanan segera diproses 1–2 hari kerja.\n"
            . "Info lebih lanjut hubungi: wa.me/6285188789052";
        sendWhatsApp($fonnteToken, $custPhone, $msgCustomer);
    }

    // ── C. Email ke ADMIN ─────────────────────────────────
    if (!empty($adminEmail)) {
        $subjAdmin = "💰 Pesanan LUNAS #{$orderId} — Mocafie";
        $bodyAdmin = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto'>
          <h2 style='color:#2D6A2B'>💰 Pesanan Baru Masuk — LUNAS</h2>
          <table style='width:100%;border-collapse:collapse'>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>No. Pesanan</td><td style='padding:8px'>{$orderId}</td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Customer</td><td style='padding:8px'>{$customerName}</td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Email</td><td style='padding:8px'>{$customerEmail}</td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>WA</td><td style='padding:8px'>{$customerPhone}</td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Total</td><td style='padding:8px'><strong>{$amount}</strong></td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Metode</td><td style='padding:8px'>{$paymentType}</td></tr>
            <tr><td style='padding:8px;background:#f5f5f5;font-weight:bold'>Status</td><td style='padding:8px;color:green'><b>LUNAS ✅</b></td></tr>
          </table>
          <p>Segera proses pesanan dan input nomor resi pengiriman.</p>
        </div>";
        sendEmail($adminEmail, $subjAdmin, $bodyAdmin);
    }

    // ── D. WA ke ADMIN ────────────────────────────────────
    if (!empty($adminPhone) && !empty($fonnteToken)) {
        $msgAdmin = "🛒 *PESANAN BARU MASUK!*\n\n"
            . "📦 No: {$orderId}\n"
            . "👤 Customer: {$customerName}\n"
            . "📱 WA: {$customerPhone}\n"
            . "💰 Total: {$amount}\n"
            . "🏦 Bayar via: {$paymentType}\n\n"
            . "Status: *LUNAS ✅* — silakan proses pesanan!";
        sendWhatsApp($fonnteToken, $adminPhone, $msgAdmin);
    }

    // ── E. Log sukses ─────────────────────────────────────
    $log = date("Y-m-d H:i:s") . " | LUNAS | {$orderId} | {$customerName} | {$grossAmount}\n";
    @file_put_contents(__DIR__ . '/payment_log.txt', $log, FILE_APPEND | LOCK_EX);

    echo json_encode(["status" => "success", "message" => "Pembayaran diproses"]);

} elseif ($isPending) {

    $log = date("Y-m-d H:i:s") . " | PENDING | {$orderId} | {$customerName}\n";
    @file_put_contents(__DIR__ . '/payment_log.txt', $log, FILE_APPEND | LOCK_EX);
    echo json_encode(["status" => "pending"]);

} elseif ($isFailed) {

    // WA ke customer (info gagal)
    if (!empty($customerPhone) && !empty($fonnteToken)) {
        $custPhone = preg_replace('/[^0-9]/', '', $customerPhone);
        if (str_starts_with($custPhone, '0')) $custPhone = '62' . substr($custPhone, 1);
        $msgGagal = "❌ *Pembayaran Gagal / Kadaluarsa — Mocafie*\n\n"
            . "Halo *{$customerName}*, pembayaran untuk pesanan *{$orderId}* "
            . "tidak berhasil atau sudah kadaluarsa.\n\n"
            . "Silakan coba pesan kembali di mocafie.com atau hubungi kami: wa.me/6285188789052";
        sendWhatsApp($fonnteToken, $custPhone, $msgGagal);
    }

    $log = date("Y-m-d H:i:s") . " | GAGAL({$transactionStatus}) | {$orderId} | {$customerName}\n";
    @file_put_contents(__DIR__ . '/payment_log.txt', $log, FILE_APPEND | LOCK_EX);
    echo json_encode(["status" => "failed"]);

} else {
    echo json_encode(["status" => "ignored", "transaction_status" => $transactionStatus]);
}
?>
