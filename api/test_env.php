<?php
// api/test_env.php
// Jalankan SEKALI via browser: https://mocafie.com/api/test_env.php
// HAPUS file ini setelah testing selesai!

require_once 'config.php';

// Cegah akses publik — ganti dengan secret Anda sendiri
if (($_GET['key'] ?? '') !== 'mocafie2026test') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=UTF-8');

echo "=== DIAGNOSTIK MOCAFIE CHECKOUT ===\n\n";

// 1. Cek .env terbaca
echo "1. Midtrans Server Key: " . (MIDTRANS_SERVER_KEY ? substr(MIDTRANS_SERVER_KEY, 0, 12) . '...' : '❌ KOSONG') . "\n";
echo "2. Midtrans Client Key: " . (MIDTRANS_CLIENT_KEY ? substr(MIDTRANS_CLIENT_KEY, 0, 12) . '...' : '❌ KOSONG') . "\n";
echo "3. Is Production: " . (MIDTRANS_IS_PRODUCTION ? 'true (Production)' : 'false (Sandbox)') . "\n";
echo "4. Admin Email : " . (getenv('ADMIN_EMAIL') ?: '❌ KOSONG') . "\n";
echo "5. Admin Phone : " . (getenv('ADMIN_PHONE') ?: '❌ KOSONG') . "\n";
echo "6. Fonnte Token: " . (getenv('FONNTE_TOKEN') !== 'YOUR_FONNTE_TOKEN_HERE' && getenv('FONNTE_TOKEN') ? 'SET ✅' : '❌ BELUM DI-SET') . "\n";
echo "7. RajaOngkir Key: " . (RAJAONGKIR_API_KEY ? '✅ SET' : '❌ KOSONG') . "\n";

echo "\n";

// 2. Cek PHP mail()
$testEmail = getenv('ADMIN_EMAIL') ?: 'armanpurba721@gmail.com';
$headers   = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: Mocafie Test <noreply@mocafie.com>\r\n";
$mailResult = @mail($testEmail, '[TEST] Email Mocafie Checkout', '<p>Email test berhasil dikirim dari Hostinger via PHP mail()!</p>', $headers);
echo "8. PHP mail() ke $testEmail: " . ($mailResult ? '✅ BERHASIL dikirim (cek inbox/spam!)' : '❌ GAGAL — perlu konfigurasi SMTP di Hostinger') . "\n";

echo "\n=== Selesai. Hapus file ini setelah testing! ===\n";
?>
