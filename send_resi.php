<?php
// send_resi.php (di ROOT — bukan di /api/)
// Halaman admin untuk mengirim nomor resi ke customer via Email + WA
// Akses: https://mocafie.com/send_resi.php
// Password diatur di .env → ADMIN_RESI_PASSWORD

require_once 'api/config.php';

$adminPass   = getenv('ADMIN_RESI_PASSWORD') ?: 'mocafie_admin2026';
$fonnteToken = getenv('FONNTE_TOKEN') ?: '';

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
$ordersFile = __DIR__ . '/api/orders.json';
$orders = file_exists($ordersFile)
    ? (json_decode(file_get_contents($ordersFile), true) ?: [])
    : [];

// ── Proses kirim resi ──
if ($isAuth && isset($_POST['send_resi'])) {
    $orderId     = trim($_POST['order_id']     ?? '');
    $resi        = trim($_POST['resi_number']   ?? '');
    $courier     = trim($_POST['courier']       ?? 'JNE');
    $manualEmail = trim($_POST['manual_email']  ?? '');
    $manualPhone = trim($_POST['manual_phone']  ?? '');

    $customerEmail = $orders[$orderId]['email'] ?? $manualEmail;
    $customerPhone = $orders[$orderId]['phone'] ?? $manualPhone;
    $customerName  = $orders[$orderId]['name']  ?? 'Customer';

    if (empty($orderId) || empty($resi)) {
        $error = 'Order ID dan Nomor Resi wajib diisi!';
    } elseif (empty($customerEmail) && empty($customerPhone)) {
        $error = 'Email dan/atau nomor WA customer tidak ditemukan. Isi manual.';
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
                <p>Pesanan Anda sudah <strong>kami kirimkan</strong>. Berikut informasi pengirimannya:</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;background:#f9fafb;border-radius:8px'>
                  <tr><td style='padding:12px 16px;font-weight:bold'>No. Pesanan</td><td style='padding:12px 16px'>{$orderId}</td></tr>
                  <tr style='background:#f0fdf4'><td style='padding:12px 16px;font-weight:bold'>Kurir</td><td style='padding:12px 16px'><strong>{$courier}</strong></td></tr>
                  <tr><td style='padding:12px 16px;font-weight:bold'>No. Resi</td><td style='padding:12px 16px'><strong style='font-size:18px;letter-spacing:1px;color:#2D6A2B'>{$resi}</strong></td></tr>
                </table>
                <div style='text-align:center;margin:20px 0'>
                  <a href='{$trackUrl}' style='background:#2D6A2B;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold'>📍 Cek Status Pengiriman</a>
                </div>
                <p style='color:#666;font-size:13px'>Pertanyaan? WA: <a href='https://wa.me/6285188789052'>0851-8878-9052</a></p>
              </div>
              <div style='background:#f5f5f5;padding:12px;text-align:center;font-size:12px;color:#999'>&copy; 2026 Mocafie — mocafie.com</div>
            </div>";
            $headers  = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: Mocafie <noreply@mocafie.com>\r\n";
            $sentEmail = @mail($customerEmail, $subject, $body, $headers);
        }

        // ── WA ke customer via Fonnte ──
        $sentWa = false;
        if (!empty($customerPhone) && !empty($fonnteToken)) {
            $phone = preg_replace('/[^0-9]/', '', $customerPhone);
            if (substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
            $msg = "📦 *Pesanan Anda Sudah Dikirim! — Mocafie*\n\n"
                 . "Halo *{$customerName}*! 🚚\n\n"
                 . "📋 No. Pesanan: {$orderId}\n"
                 . "🚚 Kurir: {$courier}\n"
                 . "📦 No. Resi: *{$resi}*\n\n"
                 . "Pantau di: {$trackUrl}\n\n"
                 . "Terima kasih sudah berbelanja di Mocafie! 🌿";
            $ch = curl_init('https://api.fonnte.com/send');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
                CURLOPT_POSTFIELDS     => http_build_query(['target' => $phone, 'message' => $msg]),
                CURLOPT_HTTPHEADER     => ["Authorization: $fonnteToken"],
                CURLOPT_TIMEOUT        => 10,
            ]);
            $res = curl_exec($ch); curl_close($ch);
            $sentWa = !empty(json_decode($res, true)['status']);
        }

        $success = "Resi berhasil dikirim!";
        if ($sentEmail) $success .= " ✉️ Email → {$customerEmail}.";
        if ($sentWa)    $success .= " 📱 WA → {$customerPhone}.";
        if (!$sentEmail && !$sentWa) $success .= " ⚠️ Email/WA tidak terkirim — cek token Fonnte & konfigurasi server.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Resi Mocafie</title>
    <meta name="robots" content="noindex,nofollow">
    
    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons (Iconify) -->
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#f2fbf5',
                            100: '#e1f5e8',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d', // Mocafie Green
                            900: '#14532d',
                        },
                        border: 'hsl(var(--border))',
                        input: 'hsl(var(--input))',
                        ring: 'hsl(var(--ring))',
                        background: 'hsl(var(--background))',
                        foreground: 'hsl(var(--foreground))',
                    }
                }
            }
        }
    </script>
    <style>
        /* Shadcn-like base styles */
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 142.1 76.2% 36.3%; /* Green ring */
            --radius: 0.5rem;
        }
        body { background-color: #f8fafc; color: hsl(var(--foreground)); }
        
        .shad-card {
            background-color: hsl(var(--background));
            border-radius: var(--radius);
            border: 1px solid hsl(var(--border));
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.025);
        }
        
        .shad-input {
            display: flex;
            height: 2.5rem;
            width: 100%;
            border-radius: var(--radius);
            border: 1px solid hsl(var(--input));
            background-color: transparent;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .shad-input:focus {
            outline: none;
            border-color: hsl(var(--ring));
            box-shadow: 0 0 0 2px rgba(21, 128, 61, 0.2);
        }
        .shad-input:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .shad-label {
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25;
            margin-bottom: 0.375rem;
            display: block;
            color: #334155;
        }
        
        .shad-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 500;
            height: 2.5rem;
            padding: 0 1rem;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .shad-btn-primary {
            background-color: hsl(var(--ring));
            color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .shad-btn-primary:hover { background-color: #166534; }
        
        .shad-btn-outline {
            border: 1px solid hsl(var(--input));
            background-color: transparent;
            color: #334155;
        }
        .shad-btn-outline:hover { background-color: #f1f5f9; color: #0f172a; }
        
        /* Custom scrollbar for table */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased">

<div class="w-full max-w-2xl">
    
    <!-- Branding Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-700 mb-4 shadow-sm">
            <span class="iconify text-2xl" data-icon="lucide:package-check"></span>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Dashboard Resi</h1>
        <p class="text-sm text-slate-500 mt-1">Sistem Logistik & Notifikasi Otomatis Mocafie</p>
    </div>

    <div class="shad-card overflow-hidden">
        
        <?php if (!$isAuth): ?>
        <!-- ================= LOGIN SCREEN ================= -->
        <div class="p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Login Diperlukan</h2>
            <p class="text-sm text-slate-500 mb-6">Masukkan password admin untuk mengakses sistem pengiriman resi.</p>
            
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 flex items-start text-red-800 text-sm">
                    <span class="iconify text-red-500 mr-2 shrink-0 h-4 w-4 mt-0.5" data-icon="lucide:alert-circle"></span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="shad-label">Password Admin</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <span class="iconify w-4 h-4" data-icon="lucide:lock"></span>
                        </span>
                        <input type="password" name="password" required autofocus placeholder="••••••••" class="shad-input pl-9">
                    </div>
                </div>
                <button type="submit" class="shad-btn shad-btn-primary w-full">
                    Masuk
                </button>
            </form>
        </div>

        <?php else: ?>
        <!-- ================= DASHBOARD SCREEN ================= -->
        
        <!-- Header Actions -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-500 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
                <span class="text-xs font-medium text-slate-600 uppercase tracking-wider">Sistem Aktif</span>
            </div>
            
            <form method="POST" class="m-0">
                <button name="logout" class="shad-btn shad-btn-outline h-8 text-xs px-3 w-full sm:w-auto">
                    <span class="iconify mr-1.5" data-icon="lucide:log-out"></span> Keluar
                </button>
            </form>
        </div>

        <div class="p-6 sm:p-8">
            <!-- Notifications -->
            <?php if ($success): ?>
                <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 flex items-start text-green-800 text-sm">
                    <span class="iconify text-green-500 mr-2 shrink-0 h-4 w-4 mt-0.5" data-icon="lucide:check-circle-2"></span>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 flex items-start text-red-800 text-sm">
                    <span class="iconify text-red-500 mr-2 shrink-0 h-4 w-4 mt-0.5" data-icon="lucide:alert-circle"></span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Tabs Navigation -->
            <div class="inline-flex h-10 items-center justify-center rounded-md bg-slate-100 p-1 text-slate-500 mb-8 w-full sm:w-auto">
                <button class="tab-btn active inline-flex items-center justify-center whitespace-nowrap rounded-sm px-4 py-1.5 text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-50 w-1/2 sm:w-auto" onclick="showTab('form', this)">
                    <span class="iconify mr-2" data-icon="lucide:send"></span> Kirim Resi
                </button>
                <button class="tab-btn inline-flex items-center justify-center whitespace-nowrap rounded-sm px-4 py-1.5 text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-50 w-1/2 sm:w-auto" onclick="showTab('list', this)">
                    <span class="iconify mr-2" data-icon="lucide:list-ordered"></span> Pesanan (<?= count($orders) ?>)
                </button>
            </div>

            <!-- Tab: FORM (Dispatch) -->
            <div id="tab-form" class="space-y-6">
                <!-- Search Box -->
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg">
                    <h3 class="text-sm font-medium text-slate-800 mb-3 flex items-center">
                        <span class="iconify mr-1.5 text-slate-400" data-icon="lucide:search"></span> Cari Data Pesanan
                    </h3>
                    <form method="GET" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <span class="iconify w-4 h-4" data-icon="lucide:hash"></span>
                            </span>
                            <input type="text" name="search" placeholder="Masukkan ID Pesanan (mis. MCF-...)" value="<?= htmlspecialchars($searchId) ?>" class="shad-input pl-9 bg-white">
                        </div>
                        <button type="submit" class="shad-btn shad-btn-outline shrink-0">Cari Pesanan</button>
                    </form>
                </div>

                <!-- Order Details Card -->
                <?php if ($order): ?>
                <div class="border border-slate-200 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase">ID Pesanan</p>
                            <p class="text-sm font-medium text-slate-900 font-mono mt-0.5"><?= htmlspecialchars($order['order_id']) ?></p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20"> Lunas </span>
                            <p class="text-sm font-semibold text-slate-900 mt-1">Rp <?= number_format($order['amount'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-start">
                            <span class="iconify text-slate-400 mt-0.5 mr-2 shrink-0" data-icon="lucide:user"></span>
                            <div>
                                <p class="font-medium text-slate-700"><?= htmlspecialchars($order['name']) ?></p>
                                <p class="text-slate-500"><?= htmlspecialchars($order['email']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <span class="iconify text-slate-400 mt-0.5 mr-2 shrink-0" data-icon="lucide:phone"></span>
                            <div>
                                <p class="text-slate-700"><?= htmlspecialchars($order['phone']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($order['resi']): ?>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center text-sm">
                            <span class="iconify text-amber-500 mr-2" data-icon="lucide:info"></span>
                            <span class="text-slate-600">Resi Saat Ini: <strong class="text-slate-900"><?= htmlspecialchars($order['resi']) ?></strong> (<?= htmlspecialchars($order['courier']) ?>)</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Dispatch Form -->
                <form method="POST" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="shad-label">ID Pesanan <span class="text-red-500">*</span></label>
                            <input type="text" name="order_id" required placeholder="MCF-XXX" value="<?= htmlspecialchars($order['order_id'] ?? $searchId) ?>" class="shad-input font-mono">
                        </div>
                        
                        <div>
                            <label class="shad-label">Nomor Resi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                    <span class="iconify w-4 h-4" data-icon="lucide:barcode"></span>
                                </span>
                                <input type="text" name="resi_number" required placeholder="mis. 100029381920" class="shad-input pl-9 font-mono font-medium">
                            </div>
                        </div>
                        
                        <div>
                            <label class="shad-label">Jasa Kurir</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                    <span class="iconify w-4 h-4" data-icon="lucide:truck"></span>
                                </span>
                                <select name="courier" class="shad-input pl-9 appearance-none">
                                    <option>JNE</option>
                                    <option>JNE Kargo JTR</option>
                                    <option>J&T Express</option>
                                    <option>SiCepat</option>
                                    <option>Anteraja</option>
                                    <option>Pos Indonesia</option>
                                    <option>Wahana</option>
                                    <option>Ninja Xpress</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                    <span class="iconify w-4 h-4" data-icon="lucide:chevron-down"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!$order): ?>
                    <div class="rounded-md border border-amber-200 bg-amber-50 p-4 mt-6">
                        <div class="flex">
                            <div class="shrink-0">
                                <span class="iconify h-5 w-5 text-amber-500" data-icon="lucide:triangle-alert"></span>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-amber-800">Pesanan belum masuk database!</h3>
                                <div class="mt-2 text-sm text-amber-700">
                                    <p>Tolong masukkan data nomor dan email pelanggan secara manual agar sistem bisa mengirim WA/Email.</p>
                                </div>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                     <div>
                                        <label class="block text-xs font-semibold text-amber-900 mb-1">Email Pelanggan</label>
                                        <input type="email" name="manual_email" placeholder="contoh@gmail.com" class="shad-input bg-white/50 border-amber-300 h-8 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-amber-900 mb-1">Nomor WhatsApp</label>
                                        <input type="text" name="manual_phone" placeholder="08xxxxxxxxxx" class="shad-input bg-white/50 border-amber-300 h-8 text-xs">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="pt-2">
                        <button type="submit" name="send_resi" class="shad-btn shad-btn-primary w-full shadow-sm hover:shadow transition-shadow">
                            <span class="iconify mr-2 h-4 w-4" data-icon="lucide:bell-ring"></span>
                            Kirim Notifikasi Resi (Email & WA)
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: LIST (Orders) -->
            <div id="tab-list" class="hidden">
                <div class="border rounded-md overflow-hidden">
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-medium">ID Pesanan</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Pelanggan</th>
                                    <th scope="col" class="px-6 py-3 font-medium whitespace-nowrap">Total Tagihan</th>
                                    <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="iconify h-8 w-8 text-slate-300 mb-2" data-icon="lucide:inbox"></span>
                                            <p>Belum ada riwayat pesanan.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach (array_reverse($orders) as $o): ?>
                                    <tr class="bg-white hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 font-mono font-medium text-primary-700">
                                            <a href="?search=<?= urlencode($o['order_id']) ?>" class="hover:underline flex items-center">
                                                <?= htmlspecialchars($o['order_id']) ?>
                                                <span class="iconify ml-1 w-3 h-3 text-slate-400" data-icon="lucide:external-link"></span>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-slate-900"><?= htmlspecialchars($o['name']) ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                                            Rp <?= number_format($o['amount'], 0, ',', '.') ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($o['resi']): ?>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 whitespace-nowrap">
                                                    <span class="iconify w-3 h-3" data-icon="lucide:check"></span> Terkirim
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 whitespace-nowrap">
                                                    <span class="iconify w-3 h-3" data-icon="lucide:clock"></span> Menunggu Resi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Footer -->
    <div class="text-center mt-6 text-xs text-slate-400">
        &copy; <?= date('Y') ?> Mocafie. Sistem didukung oleh PHP & Fonnte WhatsApp API.
    </div>
</div>

<script>
// Styling helper for the custom tabs
document.head.insertAdjacentHTML("beforeend", `
<style>
.tab-btn.active { background-color: white; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
</style>
`);

function showTab(tabName, btnElement) {
    // Hide all tab panes
    document.getElementById('tab-form').classList.add('hidden');
    document.getElementById('tab-list').classList.add('hidden');
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show the selected pane & activate button
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    btnElement.classList.add('active');
}
</script>
</body>
</html>
