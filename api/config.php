<?php
// api/config.php

// ============================================================
//  ENVIRONMENT LOADER
//  Membaca file .env dari root project dan mendefinisikan
//  konstanta konfigurasi. Tidak ada nilai sensitif di sini.
// ============================================================

function loadEnv($filePath) {
    if (!file_exists($filePath)) return;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Lewati komentar dan baris kosong
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Hapus tanda petik jika ada
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        if (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];

        if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Load .env dari root project (satu level di atas folder api/)
loadEnv(__DIR__ . '/../.env');

// --- Konstanta RajaOngkir ---
define('RAJAONGKIR_API_KEY', getenv('RAJAONGKIR_API_KEY') ?: '');
define('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1/');

// --- Konstanta Midtrans ---
define('MIDTRANS_SERVER_KEY',    getenv('MIDTRANS_SERVER_KEY')    ?: '');
define('MIDTRANS_CLIENT_KEY',    getenv('MIDTRANS_CLIENT_KEY')    ?: '');
define('MIDTRANS_IS_PRODUCTION', filter_var(getenv('MIDTRANS_IS_PRODUCTION') ?: 'false', FILTER_VALIDATE_BOOLEAN));

// ============================================================
//  CORS HELPER
//  Panggil di awal setiap file API agar hanya mocafie.com
//  yang bisa melakukan fetch ke endpoint PHP ini.
// ============================================================
function addCorsHeaders() {
    $allowedOrigins = [
        'https://mocafie.com',
        'https://www.mocafie.com',
        // Untuk lokal development, tambahkan:
        'http://localhost',
        'http://127.0.0.1',
        'http://localhost:5500',  // Live Server VSCode
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        // Kalau origin tidak dikenal, tetap izinkan untuk menghindari
        // masalah saat deployment tapi tanpa credentials
        header('Access-Control-Allow-Origin: https://mocafie.com');
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Access-Control-Max-Age: 86400'); // cache preflight 24 jam

    // Tangani preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    // Security headers tambahan
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ============================================================
//  RATE LIMITER
//  Membatasi jumlah request per IP per aksi dalam time window.
//  Menggunakan file temp agar tidak butuh database/Redis.
// ============================================================
function checkRateLimit($action, $maxRequests = 10, $windowSeconds = 60) {
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // Anonimkan IP untuk privasi
    $hash    = md5($ip . $action . date('Ymd'));
    $tmpFile = sys_get_temp_dir() . '/mocafie_rl_' . $hash . '.json';

    $now  = time();
    $data = ['count' => 0, 'reset_at' => $now + $windowSeconds];

    if (file_exists($tmpFile)) {
        $raw = @file_get_contents($tmpFile);
        if ($raw) {
            $decoded = json_decode($raw, true);
            if ($decoded && $decoded['reset_at'] > $now) {
                $data = $decoded;
            }
        }
    }

    if ($data['count'] >= $maxRequests) {
        $retryAfter = $data['reset_at'] - $now;
        header('Retry-After: ' . $retryAfter);
        header('Content-Type: application/json');
        http_response_code(429);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Terlalu banyak permintaan. Coba lagi dalam ' . $retryAfter . ' detik.'
        ]);
        exit;
    }

    $data['count']++;
    @file_put_contents($tmpFile, json_encode($data), LOCK_EX);
    return true;
}

// ============================================================
//  RAJAONGKIR cURL HELPER
// ============================================================
function callRajaOngkir($endpoint, $method = 'GET', $postFields = '') {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => RAJAONGKIR_BASE_URL . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_USERAGENT      => 'Mocafie/1.0',
        CURLOPT_HTTPHEADER     => [
            'content-type: application/x-www-form-urlencoded',
            'key: ' . RAJAONGKIR_API_KEY,
        ],
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return json_encode([
            'rajaongkir' => [
                'status' => [
                    'code'        => 500,
                    'description' => 'cURL Error: ' . $err,
                ],
            ],
        ]);
    }

    return $response;
}
