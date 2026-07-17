<?php
/**
 * Pollinations AI API Configuration
 *
 * Enter your Pollinations AI API key below.
 * Get your key from: https://enter.pollinations.ai/sign-in
 *
 * NOTE: If you leave the key empty, the application will still work
 * because Pollinations AI provides free public endpoints (with rate limits).
 * A key unlocks higher limits and priority access.
 */

// -----------------------------------------------------------------------------
// YOUR POLLINATIONS API KEY  (leave "" to use free public endpoints)
// -----------------------------------------------------------------------------
define('POLLINATIONS_API_KEY', 'PASTE HERE API KEY');

// -----------------------------------------------------------------------------
// API Endpoints
// -----------------------------------------------------------------------------
define(
    'POLLINATIONS_TEXT_ENDPOINT',
    'https://gen.pollinations.ai/v1/chat/completions'
);

define(
    'POLLINATIONS_IMAGE_ENDPOINT',
    'https://gen.pollinations.ai/image/'
);

// -----------------------------------------------------------------------------
// Default model names (can be overridden per-request)
// -----------------------------------------------------------------------------
define('POLLINATIONS_TEXT_MODEL',  'openai');
define('POLLINATIONS_IMAGE_MODEL', 'flux');

// -----------------------------------------------------------------------------
// Network Settings
// -----------------------------------------------------------------------------
define('POLLINATIONS_TIMEOUT',      120);   // seconds
define('POLLINATIONS_MAX_RETRIES',  2);
define('POLLINATIONS_RETRY_DELAY',  2);     // seconds between retries
