<?php
// api/config.php

// Masukkan API Key RajaOngkir di sini
// (Key Komerce Sandbox/Production)
define('RAJAONGKIR_API_KEY', 'bItZqmPX3a87723348ce1f25LYmV5pXG');
define('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1/');

// Konfigurasi Midtrans
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR_SERVER_KEY_HERE');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-YOUR_CLIENT_KEY_HERE');
// Set true untuk Mode Produksi (Live)
define('MIDTRANS_IS_PRODUCTION', false);

function callRajaOngkir($endpoint, $method = 'GET', $postFields = '') {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => RAJAONGKIR_BASE_URL . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            "key: " . RAJAONGKIR_API_KEY
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return json_encode([
            "rajaongkir" => [
                "status" => [
                    "code" => 500,
                    "description" => "cURL Error #:" . $err
                ]
            ]
        ]);
    } else {
        return $response;
    }
}
