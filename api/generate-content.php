<?php
/**
 * Turn ONE outline slide into full presentation-ready content:
 *   - polished title
 *   - subtitle (if applicable)
 *   - up to 7 bullet points
 *   - one short paragraph
 *   - speaker notes
 *   - key stats (if the layout is statistics)
 *   - refined image prompt
 */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('POST required', 405);

$in = read_json_input();

$topic    = sanitize_text($in['topic']    ?? '', 300);
$audience = sanitize_text($in['audience'] ?? 'general audience', 100);
$tone     = sanitize_text($in['tone']     ?? 'professional', 60);
$language = sanitize_text($in['language'] ?? 'English', 40);
$slide    = is_array($in['slide'] ?? null) ? $in['slide'] : null;

if (!$slide || empty($slide['title'])) json_error('Slide data missing.');

$layout      = $slide['layout']       ?? 'bullets';
$title       = sanitize_text($slide['title'], 120);
$summary     = sanitize_text($slide['summary'] ?? '', 600);
$imagePrompt = sanitize_text($slide['image_prompt'] ?? '', 400);

$system = "You are a professional slide-content writer. Return valid JSON only.";

$prompt = <<<PROMPT
Write the content for ONE presentation slide.

Presentation topic: $topic
Audience:           $audience
Tone:               $tone
Language:           $language
Slide layout:       $layout
Working title:      $title
Slide summary:      $summary

Return JSON with this exact shape:

{
  "title":        "punchy title, max 8 words",
  "subtitle":     "optional one-line subtitle (empty string if not needed)",
  "paragraph":    "one short paragraph (1-2 sentences) OR empty string",
  "bullets":      ["3 to 6 short bullet points, each under 14 words"],
  "stats":        [ {"value":"42%","label":"short label"} ],
  "quote":        {"text":"","author":""},
  "comparison":   {"left_title":"","left":[],"right_title":"","right":[]},
  "timeline":     [ {"label":"","text":""} ],
  "process":      [ {"step":"1","label":"","text":""} ],
  "takeaways":    ["short key takeaway"],
  "notes":        "speaker notes, 2-4 sentences",
  "image_prompt": "refined image description for AI image generation"
}

Rules:
- Fill only the fields that fit the layout. Others stay empty ("" or []).
- No markdown, no code fences.
- Keep bullets short and punchy.
- Never repeat the title inside the bullets.
- Content language: $language.
PROMPT;

try {
    $raw  = pollinations_text($prompt, ['system' => $system, 'json' => true, 'temperature' => 0.7]);
    $data = parse_json_loose($raw);
    if (!$data) throw new Exception('AI did not return valid slide content.');

    // Normalise
    $out = [
        'title'    => sanitize_text($data['title']    ?? $title, 120),
        'subtitle' => sanitize_text($data['subtitle'] ?? '', 200),
        'paragraph'=> sanitize_text($data['paragraph']?? '', 600),
        'bullets'  => [],
        'stats'    => [],
        'quote'    => ['text' => '', 'author' => ''],
        'comparison' => ['left_title'=>'','left'=>[],'right_title'=>'','right'=>[]],
        'timeline' => [],
        'process'  => [],
        'takeaways'=> [],
        'notes'    => sanitize_text($data['notes'] ?? '', 1200),
        'image_prompt' => sanitize_text($data['image_prompt'] ?? $imagePrompt, 400),
    ];
    foreach ((array)($data['bullets'] ?? []) as $b) {
        $b = sanitize_text(is_string($b) ? $b : '', 200);
        if ($b !== '') $out['bullets'][] = $b;
    }
    $out['bullets'] = array_slice($out['bullets'], 0, 7);

    foreach ((array)($data['stats'] ?? []) as $s) {
        if (!is_array($s)) continue;
        $out['stats'][] = [
            'value' => sanitize_text($s['value'] ?? '', 20),
            'label' => sanitize_text($s['label'] ?? '', 80),
        ];
    }
    if (is_array($data['quote'] ?? null)) {
        $out['quote']['text']   = sanitize_text($data['quote']['text']   ?? '', 400);
        $out['quote']['author'] = sanitize_text($data['quote']['author'] ?? '', 120);
    }
    if (is_array($data['comparison'] ?? null)) {
        $c = $data['comparison'];
        $out['comparison']['left_title']  = sanitize_text($c['left_title']  ?? '', 60);
        $out['comparison']['right_title'] = sanitize_text($c['right_title'] ?? '', 60);
        foreach ((array)($c['left']  ?? []) as $x) $out['comparison']['left'][]  = sanitize_text($x, 160);
        foreach ((array)($c['right'] ?? []) as $x) $out['comparison']['right'][] = sanitize_text($x, 160);
    }
    foreach ((array)($data['timeline'] ?? []) as $t) {
        if (!is_array($t)) continue;
        $out['timeline'][] = [
            'label' => sanitize_text($t['label'] ?? '', 40),
            'text'  => sanitize_text($t['text']  ?? '', 200),
        ];
    }
    foreach ((array)($data['process'] ?? []) as $t) {
        if (!is_array($t)) continue;
        $out['process'][] = [
            'step'  => sanitize_text($t['step']  ?? '', 4),
            'label' => sanitize_text($t['label'] ?? '', 60),
            'text'  => sanitize_text($t['text']  ?? '', 200),
        ];
    }
    foreach ((array)($data['takeaways'] ?? []) as $x) {
        $x = sanitize_text($x, 200);
        if ($x !== '') $out['takeaways'][] = $x;
    }

    json_success(['content' => $out]);
} catch (Throwable $e) {
    app_log('generate-content: ' . $e->getMessage(), 'ERROR');
    json_error('Slide content generation failed: ' . $e->getMessage(), 500);
}
