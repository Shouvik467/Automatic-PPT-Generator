<?php
/**
 * Generate a structured presentation outline from the user's form input.
 * Returns JSON: { success, outline: { title, subtitle, slides: [...] } }
 */
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST required', 405);
}

$in = read_json_input();

$topic       = sanitize_text($in['topic']        ?? '', 300);
$title       = sanitize_text($in['title']        ?? '', 200);
$description = sanitize_text($in['description']  ?? '', 800);
$slideCount  = max(3, min(30, (int)($in['slideCount'] ?? 10)));
$audience    = sanitize_text($in['audience']     ?? 'general audience', 100);
$type        = sanitize_text($in['type']         ?? 'business', 60);
$tone        = sanitize_text($in['tone']         ?? 'professional', 60);
$language    = sanitize_text($in['language']     ?? 'English', 40);
$theme       = sanitize_text($in['theme']        ?? 'modern', 40);
$imageStyle  = sanitize_text($in['imageStyle']   ?? 'professional', 40);

if ($topic === '') json_error('Topic is required.');
if ($title === '') $title = $topic;

$system = "You are an expert presentation designer. You always return valid JSON only, with no markdown fences and no commentary.";

$prompt = <<<PROMPT
Create a structured presentation outline.

Topic:            $topic
Working Title:    $title
Description:      $description
Number of Slides: $slideCount
Audience:         $audience
Type:             $type
Tone:             $tone
Language:         $language
Visual Theme:     $theme
Image Style:      $imageStyle

Return a JSON object with EXACTLY this shape (no extra keys):

{
  "title":    "final polished presentation title",
  "subtitle": "one-line subtitle",
  "slides": [
    {
      "index":       1,
      "title":       "short slide title (max 8 words)",
      "purpose":     "one-line purpose of this slide",
      "summary":     "2-sentence description of what the slide will cover",
      "layout":      "one of: title | title-subtitle | bullets | text-image | image-text | two-column | full-image | image-overlay | quote | statistics | comparison | timeline | process | features | problem-solution | section-divider | takeaways | thank-you",
      "image_prompt":"a short vivid image description suitable for AI image generation"
    }
  ]
}

Rules:
- Produce EXACTLY $slideCount slides.
- Slide 1 MUST use layout "title" (title slide).
- Slide 2 SHOULD be an introduction (bullets or text-image).
- Slide 3 SHOULD be an agenda (bullets).
- The LAST slide MUST use layout "thank-you".
- Include at least one "statistics", one "comparison" or "features", and one "takeaways" layout when possible.
- All content in $language.
- Do not repeat slide titles.
- Return ONLY the JSON — no backticks, no prose.
PROMPT;

try {
    $raw = pollinations_text($prompt, ['system' => $system, 'json' => true, 'temperature' => 0.6]);
    $data = parse_json_loose($raw);
    if (!$data || empty($data['slides']) || !is_array($data['slides'])) {
        throw new Exception('AI did not return a valid outline.');
    }

    // Normalise
    $slides = [];
    foreach (array_values($data['slides']) as $i => $s) {
        $slides[] = [
            'index'        => $i + 1,
            'title'        => sanitize_text($s['title']  ?? ('Slide ' . ($i + 1)), 120),
            'purpose'      => sanitize_text($s['purpose']?? '', 240),
            'summary'      => sanitize_text($s['summary']?? '', 600),
            'layout'       => sanitize_text($s['layout'] ?? 'bullets', 40),
            'image_prompt' => sanitize_text($s['image_prompt'] ?? '', 400),
        ];
    }

    // Ensure title/thank-you framing
    if (!empty($slides))               $slides[0]['layout']  = 'title';
    if (count($slides) > 1)            $slides[count($slides)-1]['layout'] = 'thank-you';

    json_success([
        'outline' => [
            'title'    => sanitize_text($data['title']    ?? $title, 200),
            'subtitle' => sanitize_text($data['subtitle'] ?? '', 200),
            'slides'   => $slides,
            'meta'     => compact('topic','audience','type','tone','language','theme','imageStyle'),
        ]
    ]);
} catch (Throwable $e) {
    app_log('generate-outline: ' . $e->getMessage(), 'ERROR');
    json_error('Failed to generate outline. ' . $e->getMessage(), 500);
}
