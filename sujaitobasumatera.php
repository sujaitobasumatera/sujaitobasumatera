<?php
/**
 * Fetch & extract all image URLs from sujaitobasumatera.com
 * Improved version: cURL, timeout, error handling, URL normalization, dedup, JSON output
 */

$baseUrl = 'https://sujaitobasumatera.com/';

// ── 1. Fetch halaman dengan cURL ─────────────────────────────────────────────
$ch = curl_init($baseUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; SujaiImageFetcher/1.0)',
    CURLOPT_SSL_VERIFYPEER => true,
]);
$html     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr || !$html) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Gagal mengambil halaman.', 'detail' => $curlErr], JSON_PRETTY_PRINT);
    exit;
}

if ($httpCode >= 400) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode(['error' => "HTTP $httpCode dari server target."], JSON_PRETTY_PRINT);
    exit;
}

// ── 2. Ekstrak src, srcset, data-src (lazy load) ─────────────────────────────
$patterns = [
    '/\bsrc\s*=\s*"([^"]+)"/i',
    "/\\bsrc\\s*=\\s*'([^']+)'/i",
    '/\bsrcset\s*=\s*"([^"]+)"/i',
    "/\\bsrcset\\s*=\\s*'([^']+)'/i",
    '/\bdata-src\s*=\s*"([^"]+)"/i',
    "/\\bdata-src\\s*=\\s*'([^']+)'/i",
];

$raw = [];
foreach ($patterns as $pattern) {
    if (preg_match_all($pattern, $html, $m)) {
        $raw = array_merge($raw, $m[1]);
    }
}

// ── 3. Normalisasi & filter ───────────────────────────────────────────────────
$base = rtrim($baseUrl, '/');
$imgs = [];

foreach ($raw as $src) {
    // Untuk srcset: ambil URL pertama saja (sebelum spasi/koma)
    $src = trim(preg_split('/[\s,]+/', trim($src))[0]);

    // Normalisasi URL
    if (str_starts_with($src, 'data:'))       continue; // skip data URI
    if (str_starts_with($src, 'javascript:')) continue;
    if (empty($src))                          continue;

    if (str_starts_with($src, '//'))   $src = 'https:' . $src;
    elseif (str_starts_with($src, '/')) $src = $base . $src;
    elseif (!str_starts_with($src, 'http')) $src = $base . '/' . $src;

    // Hanya simpan yang terlihat seperti gambar
    if (!preg_match('/\.(jpg|jpeg|png|gif|webp|svg|avif)(\?.*)?$/i', $src)) continue;

    $imgs[] = $src;
}

// ── 4. Hapus duplikat & urutkan ──────────────────────────────────────────────
$imgs = array_values(array_unique($imgs));
sort($imgs);

// ── 5. Output JSON bersih ────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'source'      => $baseUrl,
    'fetched_at'  => date('Y-m-d H:i:s'),
    'http_status' => $httpCode,
    'total'       => count($imgs),
    'images'      => $imgs,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
