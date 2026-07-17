<?php
/**
 * Simple ping to Pollinations to confirm the API is reachable.
 */
require_once __DIR__ . '/../includes/functions.php';

try {
    $t0 = microtime(true);
    $out = pollinations_text(
    'Reply with exactly the single word OK.',
    ['temperature' => 0]
);

$clean = trim($out, " \t\n\r\0\x0B\"'");

if (strcasecmp($clean, 'OK') !== 0) {
    throw new Exception(
        'Unexpected API response: ' . substr($out, 0, 200)
    );
}
    $ms  = (int)((microtime(true) - $t0) * 1000);
    json_success([
        'reachable' => true,
        'response'  => substr($out, 0, 80),
        'latency_ms'=> $ms,
        'key_set'   => defined('POLLINATIONS_API_KEY') && POLLINATIONS_API_KEY !== '',
    ]);
} catch (Throwable $e) {
    json_error('Could not reach Pollinations AI: ' . $e->getMessage(), 502, ['reachable' => false]);
}
