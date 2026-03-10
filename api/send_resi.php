<?php
// api/send_resi.php
// Halaman admin untuk mengirim nomor resi ke customer via Email + WA
// Akses: https://mocafie.com/api/send_resi.php
// Password diatur di .env → ADMIN_RESI_PASSWORD

require_once 'config.php';

$adminPass   = getenv('ADMIN_RESI_PASSWORD') ?: 'mocafie_admin2026';
$fonnteToken = getenv('FONNTE_TOKEN') ?: '';
$adminEmail  = getenv('ADMIN_EMAIL')  ?: '';

// ── Autentikasi sesi sederhana ──
session_start();
$error   = '';
$success = '';
$order   = null;

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: send_resi.php');
    exit;
}

if (isset($_POST['password']) && !isset($_SESSION['resi_auth'])) {
    if ($_POST['password'] === $adminPass) {
        $_SESSION['resi_auth'] = true;
    } else {
        $error = 'Password salah!';
    }
}

$isAuth = $_SESSION['resi_auth'] ?? false;

// ── Load data orders ──
$ordersFile = __DIR__ . '/orders.json';
$orders = file_exists($ordersFile)
    ? (json_decode(file_get_contents($ordersFile), true) ?: [])
    : [];

// ── Proses kirim resi ──
if ($isAuth && isset($_POST['send_resi'])) {
    $orderId   = trim($_POST['order_id'] ?? '');
    $resi      = trim($_POST['resi_number'] ?? '');
    $courier   = trim($_POST['courier'] ?? 'JNE');
    $manualEmail = trim($_POST['manual_email'] ?? '');
    $manualPhone = trim($_POST['manual_phone'] ?? '');

    // Cek order dari JSON atau gunakan input manual
    $customerEmail = $orders[$orderId]['email'] ?? $manualEmail;
    $customerPhone = $orders[$orderId]['phone'] ?? $manualPhone;
    $customerName  = $orders[$orderId]['name']  ?? 'Customer';

    if (empty($orderId) || empty($resi)) {
        $error = 'Order ID dan Nomor Resi wajib diisi!';
    } elseif (empty($customerEmail) && empty($customerPhone)) {
        $error = 'Email dan/atau nomor WA customer tidak ditemukan. Isi manual di bawah.';
    } else {
        // Simpan resi ke orders.json
        if (isset($orders[$orderId])) {
            $orders[$orderId]['resi']    = $resi;
            $orders[$orderId]['courier'] = $courier;
            $orders[$orderId]['status']  = 'RESI_SENT';
            @file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        $trackUrl = "https://cekresi.com/?resi={$resi}";

        // ── Email ke customer ──
        $sentEmail = false;
        if (!empty($customerEmail)) {
            $subject = "📦 Pesanan #{$orderId} Sudah Dikirim! — Mocafie";
            $body    = "
            <div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden'>
              <div style='background:#2D6A2B;padding:24px;text-align:center'>
                <h1 style='color:#fff;margin:0;font-size:22px'>🌿 Mocafie</h1>
                <p style='color:#c8e6c9;margin:4px 0 0'>Tepung Mocaf Premium</p>
              </div>
              <div style='padding:24px'>
                <p>Halo <strong>{$customerName}</strong>, kabar baik! 📦</p>
                <p>Pesanan Anda sudah <strong>kami kirimkan</strong> hari ini. Berikut informasi pengirimannya:</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;background:#f9fafb;border-radius:8px'>
                  <tr><td style='padding:12px 16px;font-weight:bold;color:#374151'>No. Pesanan</td><td style='padding:12px 16px'>{$orderId}</td></tr>
                  <tr style='background:#f0fdf4'><td style='padding:12px 16px;font-weight:bold;color:#374151'>Kurir</td><td style='padding:12px 16px'><strong>{$courier}</strong></td></tr>
                  <tr><td style='padding:12px 16px;font-weight:bold;color:#374151'>No. Resi</td><td style='padding:12px 16px'><strong style='font-size:18px;letter-spacing:1px;color:#2D6A2B'>{$resi}</strong></td></tr>
                </table>
                <div style='text-align:center;margin:20px 0'>
                  <a href='{$trackUrl}' style='background:#2D6A2B;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px'>📍 Cek Status Pengiriman</a>
                </div>
                <p style='color:#666;font-size:13px'>Pertanyaan? Hubungi kami via WhatsApp: <a href='https://wa.me/6285188789052'>0851-8878-9052</a></p>
              </div>
              <div style='background:#f5f5f5;padding:12px;text-align:center;font-size:12px;color:#999'>
                &copy; 2026 Mocafie Indonesia — mocafie.com
              </div>
            </div>";
            $headers  = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Mocafie <noreply@mocafie.com>\r\n";
            $sentEmail = @mail($customerEmail, $subject, $body, $headers);
        }

        // ── WA ke customer ──
        $sentWa = false;
        if (!empty($customerPhone) && !empty($fonnteToken)) {
            $phone = preg_replace('/[^0-9]/', '', $customerPhone);
            if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
            $msg = "📦 *Pesanan Anda Sudah Dikirim! — Mocafie*\n\n"
                 . "Halo *{$customerName}*! Pesanan Anda sudah kami kirimkan. 🚚\n\n"
                 . "📋 *No. Pesanan:* {$orderId}\n"
                 . "🚚 *Kurir:* {$courier}\n"
                 . "📦 *No. Resi:* *{$resi}*\n\n"
                 . "Pantau pengiriman di: {$trackUrl}\n\n"
                 . "Terima kasih sudah berbelanja di Mocafie! 🌿";
            $ch = curl_init('https://api.fonnte.com/send');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query(['target' => $phone, 'message' => $msg]),
                CURLOPT_HTTPHEADER     => ["Authorization: $fonnteToken"],
                CURLOPT_TIMEOUT        => 10,
            ]);
            $res    = curl_exec($ch); curl_close($ch);
            $sentWa = !empty(json_decode($res, true)['status']);
        }

        $success = "Resi berhasil dikirim!";
        if ($sentEmail) $success .= " ✉️ Email terkirim ke {$customerEmail}.";
        if ($sentWa)    $success .= " 📱 WA terkirim ke {$customerPhone}.";
        if (!$sentEmail && !$sentWa) $success .= " ⚠️ Email/WA tidak terkirim — cek token Fonnte dan koneksi server.";
    }
}

// ── Cari order by ID ──
$searchId = trim($_GET['search'] ?? '');
if ($isAuth && $searchId && isset($orders[$searchId])) {
    $order = $orders[$searchId];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Kirim Resi — Mocafie</title>
<meta name="robots" content="noindex,nofollow">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #f0fdf4; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
  .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.08); max-width: 560px; width: 100%; padding: 32px; }
  h1 { color: #2D6A2B; font-size: 22px; margin-bottom: 4px; }
  .sub { color: #6b7280; font-size: 13px; margin-bottom: 24px; }
  label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 4px; margin-top: 16px; }
  input, select { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; transition: border .2s; }
  input:focus, select:focus { border-color: #2D6A2B; box-shadow: 0 0 0 3px rgba(45,106,43,.1); }
  .btn { display: block; width: 100%; padding: 12px; background: #2D6A2B; color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 24px; transition: background .2s; }
  .btn:hover { background: #1f4d1e; }
  .btn-sm { background: #e5e7eb; color: #374151; font-size: 13px; font-weight: 600; padding: 8px 16px; border-radius: 8px; border: none; cursor: pointer; display: inline-block; margin-top: 8px; }
  .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
  .alert-green { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
  .alert-red { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
  .order-info { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-top: 10px; font-size: 13px; line-height: 1.8; }
  .order-info strong { color: #111827; }
  table.orders { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
  table.orders th { background: #f0fdf4; color: #166534; padding: 8px 10px; text-align: left; font-weight: 600; }
  table.orders td { padding: 8px 10px; border-bottom: 1px solid #f0f0f0; }
  table.orders tr:last-child td { border: none; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-yellow { background: #fef9c3; color: #854d0e; }
  .tab { display: flex; gap: 8px; margin-bottom: 20px; }
  .tab-btn { padding: 8px 18px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; background: #e5e7eb; color: #374151; }
  .tab-btn.active { background: #2D6A2B; color: #fff; }
</style>
</head>
<body>
<div class="card">
  <h1>🚚 Kirim Resi — Mocafie Admin</h1>
  <p class="sub">Halaman ini hanya untuk admin. Kirim nomor resi ke customer via Email &amp; WhatsApp.</p>

  <?php if (!$isAuth): ?>
  <!-- Login Form -->
  <?php if ($error): ?><div class="alert alert-red"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Password Admin</label>
    <input type="password" name="password" required autofocus placeholder="Masukkan password admin">
    <button type="submit" class="btn">🔐 Masuk</button>
  </form>

  <?php else: ?>
  <!-- Logout -->
  <form method="POST" style="text-align:right;margin-bottom:16px">
    <button type="submit" name="logout" class="btn-sm">Keluar ↩</button>
  </form>

  <!-- Alert -->
  <?php if ($success): ?><div class="alert alert-green"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-red"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <!-- Tabs -->
  <div class="tab">
    <button class="tab-btn active" onclick="showTab('form',this)">Kirim Resi</button>
    <button class="tab-btn" onclick="showTab('list',this)">Daftar Pesanan</button>
  </div>

  <!-- Tab: Form kirim resi -->
  <div id="tab-form">
    <!-- Cari Order -->
    <form method="GET" style="display:flex;gap:8px;margin-bottom:16px">
      <input type="text" name="search" placeholder="Cari Order ID (MCF-...)" value="<?= htmlspecialchars($searchId) ?>" style="flex:1">
      <button type="submit" style="padding:10px 16px;background:#2D6A2B;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600">Cari</button>
    </form>

    <?php if ($order): ?>
    <div class="order-info">
      <strong>Order:</strong> <?= htmlspecialchars($order['order_id']) ?><br>
      <strong>Customer:</strong> <?= htmlspecialchars($order['name']) ?><br>
      <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
      <strong>WA:</strong> <?= htmlspecialchars($order['phone']) ?><br>
      <strong>Total:</strong> Rp <?= number_format($order['amount'], 0, ',', '.') ?><br>
      <?php if ($order['resi']): ?>
      <strong>Resi Sebelumnya:</strong> <?= htmlspecialchars($order['resi']) ?> (<?= htmlspecialchars($order['courier']) ?>)
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <form method="POST" style="margin-top:0">
      <label>Order ID *</label>
      <input type="text" name="order_id" required placeholder="MCF-1234567890-123" value="<?= htmlspecialchars($order['order_id'] ?? $searchId) ?>">

      <label>Nomor Resi *</label>
      <input type="text" name="resi_number" required placeholder="00000000000000">

      <label>Kurir</label>
      <select name="courier">
        <option value="JNE">JNE</option>
        <option value="JNE Kargo JTR">JNE Kargo JTR</option>
        <option value="J&T">J&amp;T Express</option>
        <option value="SiCepat">SiCepat</option>
        <option value="Anteraja">Anteraja</option>
        <option value="Pos Indonesia">Pos Indonesia</option>
      </select>

      <?php if (!$order): ?>
      <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:12px;margin-top:16px;font-size:13px;color:#92400e">
        ⚠️ Order tidak ditemukan di database. Isi manual di bawah (opsional):
      </div>
      <label>Email Customer (Manual)</label>
      <input type="email" name="manual_email" placeholder="customer@email.com">
      <label>No. WA Customer (Manual)</label>
      <input type="text" name="manual_phone" placeholder="08xxxxxxxxxx">
      <?php endif; ?>

      <button type="submit" name="send_resi" class="btn">📦 Kirim Notifikasi Resi</button>
    </form>
  </div>

  <!-- Tab: Daftar pesanan -->
  <div id="tab-list" style="display:none">
    <?php if (empty($orders)): ?>
    <p style="color:#6b7280;text-align:center;padding:20px">Belum ada pesanan masuk.</p>
    <?php else: ?>
    <table class="orders">
      <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Resi</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse($orders) as $o): ?>
      <tr>
        <td><a href="?search=<?= urlencode($o['order_id']) ?>" style="color:#2D6A2B;font-weight:600;"><?= htmlspecialchars($o['order_id']) ?></a></td>
        <td><?= htmlspecialchars($o['name']) ?></td>
        <td>Rp <?= number_format($o['amount'], 0, ',', '.') ?></td>
        <td><?= $o['resi'] ? htmlspecialchars($o['resi']) : '<span style="color:#9ca3af">-</span>' ?></td>
        <td>
          <?php if ($o['resi']): ?>
            <span class="badge badge-green">Resi Terkirim</span>
          <?php else: ?>
            <span class="badge badge-yellow">Belum Resi</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php endif; ?>
</div>

<script>
function showTab(name, btn) {
  document.getElementById('tab-form').style.display = name === 'form' ? 'block' : 'none';
  document.getElementById('tab-list').style.display = name === 'list' ? 'block' : 'none';
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>
