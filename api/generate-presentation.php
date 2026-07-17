<?php
/**
 * One-shot endpoint: outline + all slide content + all image URLs.
 * Useful if the user does not want to review an outline first.
 * (The main flow uses generate-outline + generate-content separately.)
 */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('POST required', 405);
$in = read_json_input();

// Reuse outline endpoint logic via internal call
$outlineReq = $in;
$outlineJson = null;

// Manually call the outline logic
try {
    // Simulate calling outline generator inline
    $_POST_BACKUP = null;
    // We'll just directly call the pollinations text with the same prompt shape.
    // For simplicity, forward to generate-outline via CURL to our own base URL.
    $ch = curl_init(BASE_URL . '/api/generate-outline.php');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($outlineReq),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => POLLINATIONS_TIMEOUT + 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $out = curl_exec($ch);
    curl_close($ch);
    $outlineJson = json_decode($out, true);
    if (empty($outlineJson['success'])) throw new Exception($outlineJson['error'] ?? 'Outline failed.');
    json_success(['outline' => $outlineJson['outline']]);
} catch (Throwable $e) {
    json_error('Full generation failed: ' . $e->getMessage(), 500);
}
