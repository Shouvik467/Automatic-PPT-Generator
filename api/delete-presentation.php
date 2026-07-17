<?php
require_once __DIR__ . '/../includes/functions.php';

$in = $_SERVER['REQUEST_METHOD'] === 'POST' ? read_json_input() : $_GET;
$id = safe_id($in['id'] ?? '');
if ($id === '') json_error('ID required.');
if (delete_presentation($id)) {
    json_success(['deleted' => $id]);
} else {
    json_error('Not found.', 404);
}
