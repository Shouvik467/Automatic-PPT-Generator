<?php
/**
 * Global application configuration.
 * Loaded by every page and every API endpoint.
 */

// -----------------------------------------------------------------------------
// App metadata
// -----------------------------------------------------------------------------
define('APP_NAME',    'AI PPT Generator');
define('APP_VERSION', '1.0.0');

// -----------------------------------------------------------------------------
// Paths (auto-detected)
// -----------------------------------------------------------------------------
define('BASE_PATH',    dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PRESENTATIONS_PATH',  STORAGE_PATH . '/presentations');
define('IMAGES_PATH',         STORAGE_PATH . '/generated-images');
define('UPLOADS_PATH',        STORAGE_PATH . '/uploads');
define('TEMP_PATH',           STORAGE_PATH . '/temp');
define('LOGS_PATH',           STORAGE_PATH . '/logs');

// Auto-detect base URL (works on localhost, XAMPP, WAMP, shared hosting)
if (!defined('BASE_URL')) {
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If we're inside /api/, step one directory up so BASE_URL always points to the project root.
    if (substr($scriptDir, -4) === '/api') {
        $scriptDir = substr($scriptDir, 0, -4);
    }
    $scriptDir = rtrim($scriptDir, '/');
    define('BASE_URL', $protocol . '://' . $host . $scriptDir);
}

// -----------------------------------------------------------------------------
// PHP runtime tweaks
// -----------------------------------------------------------------------------
@ini_set('display_errors', '0');
error_reporting(E_ALL);
@date_default_timezone_set('UTC');
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// -----------------------------------------------------------------------------
// Load Pollinations config
// -----------------------------------------------------------------------------
require_once __DIR__ . '/pollinations-config.php';

// -----------------------------------------------------------------------------
// Ensure storage folders exist
// -----------------------------------------------------------------------------
foreach ([STORAGE_PATH, PRESENTATIONS_PATH, IMAGES_PATH, UPLOADS_PATH, TEMP_PATH, LOGS_PATH] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}
