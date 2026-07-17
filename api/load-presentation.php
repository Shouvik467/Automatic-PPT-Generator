<?php
/**
 * GET  /api/load-presentation.php?id=xxxx     -> load one
 * GET  /api/load-presentation.php             -> list all
 */
require_once __DIR__ . '/../includes/functions.php';

$id = safe_id($_GET['id'] ?? '');
if ($id === '') {
    json_success(['presentations' => list_presentations()]);
}
$p = load_presentation($id);
if (!$p) json_error('Presentation not found.', 404);
json_success(['presentation' => $p]);
