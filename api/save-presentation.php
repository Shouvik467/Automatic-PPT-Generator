<?php
/**
 * Save (or update) a presentation as a JSON file under storage/presentations/.
 */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('POST required', 405);
$in = read_json_input();

if (empty($in['title']) && empty($in['topic'])) json_error('Missing title/topic.');
if (empty($in['slides']) || !is_array($in['slides'])) json_error('Missing slides.');

$presentation = [
    'id'         => safe_id($in['id'] ?? '') ?: generate_presentation_id(),
    'title'      => sanitize_text($in['title'] ?? ($in['topic'] ?? 'Untitled'), 200),
    'topic'      => sanitize_text($in['topic'] ?? '', 300),
    'subtitle'   => sanitize_text($in['subtitle'] ?? '', 200),
    'theme'      => sanitize_text($in['theme']  ?? 'modern', 40),
    'imageStyle' => sanitize_text($in['imageStyle'] ?? 'professional', 40),
    'aspect'     => sanitize_text($in['aspect'] ?? '16:9', 10),
    'author'     => sanitize_text($in['author'] ?? '', 120),
    'organization' => sanitize_text($in['organization'] ?? '', 120),
    'meta'       => is_array($in['meta'] ?? null) ? $in['meta'] : [],
    'slides'     => $in['slides'],
    'created_at' => $in['created_at'] ?? '',
];

try {
    $saved = save_presentation($presentation);
    json_success(['id' => $saved['id'], 'updated_at' => $saved['updated_at']]);
} catch (Throwable $e) {
    json_error('Save failed: ' . $e->getMessage(), 500);
}
