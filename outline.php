<?php
// Redirect helper: outline.php simply forwards to generator.php with state.
require_once __DIR__ . '/config/config.php';
header('Location: ' . BASE_URL . '/generator.php');
exit;
