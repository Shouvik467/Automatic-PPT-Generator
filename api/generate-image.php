<?php
/**
 * Build a Pollinations image URL for a slide.
 * We return a direct Pollinations URL (fast, cacheable). If ?download=1 is
 * passed, the image is fetched server-side and stored locally.
 */
require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$in = $method === 'POST' ? read_json_input() : $_GET;

$prompt     = sanitize_text($in['prompt']     ?? '', 800);
$style      = sanitize_text($in['style']      ?? 'professional', 60);
$title      = sanitize_text($in['title']      ?? '', 200);
$topic      = sanitize_text($in['topic']      ?? '', 200);
$aspect     = sanitize_text($in['aspect']     ?? '16:9', 10);
$download   = !empty($in['download']);
$seed       = isset($in['seed']) ? (int)$in['seed'] : random_int(1, 999999);

if ($prompt === '' && $title === '' && $topic === '') json_error('Provide prompt / title / topic.');

// Full engineered prompt
$fullPrompt = "Create a high-quality professional presentation image for a slide titled \"$title\" "
            . "in a presentation about \"$topic\". "
            . ($prompt !== '' ? "Scene: $prompt. " : '')
            . "Style: $style. "
            . "Clean 16:9 landscape composition, professional lighting, presentation-friendly layout, "
            . "strong visual quality, good negative space, no watermark, no logo, no text, no distorted objects.";

// Aspect ratio → dimensions
$w = 1280; $h = 720;
if ($aspect === '4:3')  { $w = 1200; $h = 900; }
if ($aspect === '1:1')  { $w = 1024; $h = 1024; }
if ($aspect === '9:16') { $w = 720;  $h = 1280; }

try {
    $url = pollinations_image_url($fullPrompt, [
        'width'  => $w,
        'height' => $h,
        'seed'   => $seed,
    ]);

    if ($download) {
        $local = download_image_to_storage($url);
        json_success(['url' => $local, 'remote_url' => $url, 'prompt' => $fullPrompt, 'seed' => $seed]);
    } else {
        json_success(['url' => $url, 'prompt' => $fullPrompt, 'seed' => $seed]);
    }
} catch (Throwable $e) {
    app_log('generate-image: ' . $e->getMessage(), 'ERROR');
    json_error('Image generation failed: ' . $e->getMessage(), 500);
}
