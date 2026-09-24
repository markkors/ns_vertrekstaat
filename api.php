<?php
// Proxy naar de NS Reisinformatie API: houdt de API-sleutel server-side en cachet het antwoord kort.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// config.php staat niet in git (bevat de API-sleutel); maak hem aan op basis van config.example.php
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'config.php ontbreekt: kopieer config.example.php en vul je API-sleutel in']);
    exit;
}
$config = require __DIR__ . '/config.php';

$cacheFile = sys_get_temp_dir() . '/ns_departures_' . $config['station'] . '.json';
if (is_file($cacheFile) && time() - filemtime($cacheFile) < $config['cache_seconds']) {
    readfile($cacheFile);
    exit;
}

$url = 'https://gateway.apiportal.ns.nl/reisinformatie-api/api/v2/departures?' . http_build_query([
    'station'     => $config['station'],
    'maxJourneys' => $config['max_journeys'],
    'lang'        => $config['lang'],
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    // Gebruik de certificatenopslag van het OS (Windows-PHP heeft standaard geen CA-bundel)
    CURLOPT_SSL_OPTIONS    => defined('CURLSSLOPT_NATIVE_CA') ? CURLSSLOPT_NATIVE_CA : 0,
    CURLOPT_HTTPHEADER     => [
        'Ocp-Apim-Subscription-Key: ' . $config['api_key'],
        'Accept: application/json',
    ],
]);
$body   = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error  = curl_error($ch);
curl_close($ch);

if ($body === false || $status !== 200) {
    // Val terug op de laatst bekende data, zodat het scherm niet leeg wordt.
    if (is_file($cacheFile)) {
        header('X-Stale: 1');
        readfile($cacheFile);
        exit;
    }
    http_response_code(502);
    echo json_encode(['error' => $error ?: "NS API gaf status $status"]);
    exit;
}

file_put_contents($cacheFile, $body, LOCK_EX);
echo $body;
