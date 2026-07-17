<?php
/**
 * Shared helper functions for the AI PPT Generator.
 */

require_once __DIR__ . '/../config/config.php';

// -----------------------------------------------------------------------------
// Logging
// -----------------------------------------------------------------------------
function app_log($message, $level = 'INFO') {
    $line = '[' . date('Y-m-d H:i:s') . "] [$level] " . (is_string($message) ? $message : json_encode($message)) . PHP_EOL;
    @file_put_contents(LOGS_PATH . '/app.log', $line, FILE_APPEND);
}

// -----------------------------------------------------------------------------
// JSON response helpers
// -----------------------------------------------------------------------------
function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

function json_success($data = []) {
    json_response(['success' => true] + (is_array($data) ? $data : ['data' => $data]));
}

function json_error($message, $status = 400, $extra = []) {
    app_log("ERROR ($status): $message", 'ERROR');
    json_response(['success' => false, 'error' => $message] + $extra, $status);
}

// -----------------------------------------------------------------------------
// Input helpers
// -----------------------------------------------------------------------------
function read_json_input() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function sanitize_text($str, $maxLen = 5000) {
    $str = is_string($str) ? $str : '';
    $str = trim($str);
    if (strlen($str) > $maxLen) $str = substr($str, 0, $maxLen);
    return $str;
}

function safe_id($id) {
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$id);
}

// -----------------------------------------------------------------------------
// Pollinations AI — text generation
// -----------------------------------------------------------------------------
function pollinations_text($prompt, $opts = []) {
    $model       = $opts['model']       ?? POLLINATIONS_TEXT_MODEL;
    $jsonMode    = $opts['json']        ?? false;
    $temperature = $opts['temperature'] ?? 0.7;
    $system      = $opts['system']      ?? 'You are a professional presentation designer and content writer.';

    // Build OpenAI-compatible payload against Pollinations text endpoint
    $payload = [
    'model' => $model,
    'messages' => [
        [
            'role' => 'system',
            'content' => $system
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ],
    ],
    'temperature' => $temperature,
];

if ($jsonMode) {
    $payload['response_format'] = [
        'type' => 'json_object'
    ];
}

    $url = POLLINATIONS_TEXT_ENDPOINT;
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if (defined('POLLINATIONS_API_KEY') && POLLINATIONS_API_KEY !== '') {
        $headers[] = 'Authorization: Bearer ' . POLLINATIONS_API_KEY;
    }

    $lastError = '';
    for ($attempt = 0; $attempt <= POLLINATIONS_MAX_RETRIES; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => POLLINATIONS_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
            // Some endpoints return plain text
            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
    $data = json_decode($response, true);

    if (
        isset($data['choices'][0]['message']['content']) &&
        is_string($data['choices'][0]['message']['content'])
    ) {
        return trim($data['choices'][0]['message']['content']);
    }

    throw new Exception(
        'Unexpected Pollinations response: ' .
        substr($response, 0, 500)
    );
}
        }
        $lastError = "HTTP $httpCode " . $curlErr . ' ' . substr((string)$response, 0, 400);
        app_log("Pollinations text attempt " . ($attempt+1) . " failed: $lastError", 'WARN');
        if ($attempt < POLLINATIONS_MAX_RETRIES) sleep(POLLINATIONS_RETRY_DELAY);
    }

    // -------- Fallback: GET endpoint --------
    $getUrl = 'https://gen.pollinations.ai/text/'
    . rawurlencode($prompt)
    . '?model=' . urlencode($model);

if ($jsonMode) {
    $getUrl .= '&json=true';
}
    $ch = curl_init($getUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => POLLINATIONS_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($response !== false && $httpCode >= 200 && $httpCode < 300 && trim($response) !== '') {
        return trim($response);
    }

    throw new Exception('Pollinations text API failed: ' . $lastError);
}

// Parse a JSON response defensively (strip markdown fences, extract braces)
function parse_json_loose($text) {
    if (!is_string($text)) return null;
    $text = trim($text);
    // Strip ```json ... ``` fences
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/', '', $text);
    $data = json_decode($text, true);
    if (is_array($data)) return $data;
    // Try to find first { ... last }
    $start = strpos($text, '{');
    $end   = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $candidate = substr($text, $start, $end - $start + 1);
        $data = json_decode($candidate, true);
        if (is_array($data)) return $data;
    }
    // Try array
    $start = strpos($text, '[');
    $end   = strrpos($text, ']');
    if ($start !== false && $end !== false && $end > $start) {
        $candidate = substr($text, $start, $end - $start + 1);
        $data = json_decode($candidate, true);
        if (is_array($data)) return $data;
    }
    return null;
}

// -----------------------------------------------------------------------------
// Pollinations AI — image generation
// -----------------------------------------------------------------------------
function pollinations_image_url($prompt, $opts = []) {
    $width  = (int)($opts['width']  ?? 1280);
    $height = (int)($opts['height'] ?? 720);
    $seed   = (int)($opts['seed']   ?? random_int(1, 999999));
    $model  = $opts['model'] ?? POLLINATIONS_IMAGE_MODEL;

    $qs = http_build_query([
    'width'  => $width,
    'height' => $height,
    'seed'   => $seed,
    'model'  => $model,
]);
    return POLLINATIONS_IMAGE_ENDPOINT . rawurlencode($prompt) . '?' . $qs;
}

// Download an image and store it locally; return public URL
function download_image_to_storage($imageUrl, $filename = null) {
    if (!$filename) {
        $filename = 'img_' . bin2hex(random_bytes(6)) . '.jpg';
    }
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
    $dest = IMAGES_PATH . '/' . $filename;

    $headers = [];
    if (defined('POLLINATIONS_API_KEY') && POLLINATIONS_API_KEY !== '') {
        $headers[] = 'Authorization: Bearer ' . POLLINATIONS_API_KEY;
    }

    $ch = curl_init($imageUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => POLLINATIONS_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($data === false || $code < 200 || $code >= 300 || strlen($data) < 100) {
        throw new Exception("Image download failed (HTTP $code)");
    }
    file_put_contents($dest, $data);
    return BASE_URL . '/storage/generated-images/' . $filename;
}

// -----------------------------------------------------------------------------
// Presentation storage (JSON files)
// -----------------------------------------------------------------------------
function generate_presentation_id() {
    return bin2hex(random_bytes(4));
}

function presentation_file_path($id) {
    $id = safe_id($id);
    if ($id === '') return null;
    return PRESENTATIONS_PATH . '/presentation_' . $id . '.json';
}

function save_presentation($presentation) {
    if (empty($presentation['id'])) {
        $presentation['id'] = generate_presentation_id();
    }
    if (empty($presentation['created_at'])) {
        $presentation['created_at'] = date('c');
    }
    $presentation['updated_at'] = date('c');
    $path = presentation_file_path($presentation['id']);
    if (!$path) throw new Exception('Invalid presentation ID');
    file_put_contents($path, json_encode($presentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return $presentation;
}

function load_presentation($id) {
    $path = presentation_file_path($id);
    if (!$path || !file_exists($path)) return null;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

function delete_presentation($id) {
    $path = presentation_file_path($id);
    if ($path && file_exists($path)) {
        @unlink($path);
        return true;
    }
    return false;
}

function list_presentations() {
    $out = [];
    if (!is_dir(PRESENTATIONS_PATH)) return $out;
    foreach (glob(PRESENTATIONS_PATH . '/presentation_*.json') as $file) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $out[] = [
                'id'         => $data['id']         ?? '',
                'title'      => $data['title']      ?? 'Untitled',
                'topic'      => $data['topic']      ?? '',
                'theme'      => $data['theme']      ?? 'modern',
                'slide_count'=> isset($data['slides']) ? count($data['slides']) : 0,
                'created_at' => $data['created_at'] ?? '',
                'updated_at' => $data['updated_at'] ?? '',
            ];
        }
    }
    usort($out, function ($a, $b) { return strcmp($b['updated_at'], $a['updated_at']); });
    return $out;
}
