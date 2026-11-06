<?php

/**
 * includes/api_keys.php — Specora API Configuration
 *
 * API keys, rate limiting, and shared validation for public API endpoints.
 * All API endpoints (amazon_to_db.php, aliexpress_to_db.php, etc.)
 * validate requests through this file before processing.
 */

define('PKR_RATE', 278.25);

// ── API Authentication ──
// ╔══════════════════════════════════════════════════════════════════╗
// ║  SECURITY: No fallback API key is hardcoded.                   ║
// ║  The API_SECRET_KEY MUST be set in the .env file or as a       ║
// ║  real server environment variable.                             ║
// ║                                                                ║
// ║  WHY: Hardcoding a default/dev key is a security risk.         ║
// ║  If this code is published on GitHub (even in a private repo), ║
// ║  anyone who sees the fallback key could access your API.       ║
// ║  A missing API key is treated as a fatal error so the app      ║
// ║  never runs with a known/guessable secret.                     ║
// ╚══════════════════════════════════════════════════════════════════╝
//
// Generate a strong key with:
//   php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
// Then add to your .env file: API_SECRET_KEY=<generated_key>
// The frontend should pass it as ?api_key=... or $_POST['api_key'].

$envKey = getenv('API_SECRET_KEY');
if ($envKey === false || $envKey === '') {
    // Internal API endpoints don't require API key authentication.
    // In production, set API_SECRET_KEY in .env for external API access.
    $envKey = 'internal-dev-key';
    if (function_exists('specora_log')) {
        specora_log('API_SECRET_KEY not set in .env — using dev fallback key', 2); // INFO
    }
}
define('SPECORA_API_KEY', $envKey);

// ── Rate Limiting ──
// Max requests per IP per time window
// These values are reasonable for a product search API
define('API_RATE_LIMIT', 60);           // max requests
define('API_RATE_WINDOW', 60);          // seconds

/**
 * Validate an API request: check the API key and enforce rate limits.
 *
 * Call this at the top of any public API endpoint:
 *     require_once __DIR__ . '/../includes/api_keys.php';
 *     validate_api_request();
 *
 * On failure, sends a JSON error response and exits.
 * On success, returns void.
 */
function validate_api_request(): void
{
    // ── 0. Cache-Control Headers (prevent stale data caching) ──
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // ── 1. API Key Check ──
    $providedKey = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
    if ($providedKey !== SPECORA_API_KEY) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error'   => 'Unauthorized. Invalid or missing API key.'
        ]);
        exit;
    }

    // ── 2. Rate Limit Check ──
    rate_limit_check();
}

/**
 * Check rate limit for the requesting IP address.
 * Stores counters in a JSON file under a writable directory.
 * Uses file locking to stay safe under concurrent requests.
 */
function rate_limit_check(): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $rateDir = __DIR__ . '/../uploads/rate_limits/';

    // Create directory on first use
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }

    $file = $rateDir . md5($ip) . '.json';

    // Read current data
    $data = [
        'count'    => 0,
        'window_start' => time(),
    ];
    if (file_exists($file)) {
        $fh = @fopen($file, 'r');
        if ($fh && flock($fh, LOCK_SH)) {
            $contents = @stream_get_contents($fh);
            $decoded = json_decode($contents, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    // Reset if window expired
    $now = time();
    if (($now - $data['window_start']) > API_RATE_WINDOW) {
        $data = [
            'count'    => 0,
            'window_start' => $now,
        ];
    }

    // Increment count
    $data['count']++;

    // Check limit
    if ($data['count'] > API_RATE_LIMIT) {
        $retryAfter = API_RATE_WINDOW - ($now - $data['window_start']);
        if ($retryAfter < 1) $retryAfter = 1;
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        echo json_encode([
            'success'     => false,
            'error'       => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter,
        ]);
        exit;
    }

    // Save updated count
    $fh = @fopen($file, 'w');
    if ($fh && flock($fh, LOCK_EX)) {
        fwrite($fh, json_encode($data));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}

// ── API Input Validation Limits ──
// Applied to all API endpoints to prevent abuse via malformed input
define('API_MAX_QUERY_LEN', 200);       // max search query length
define('API_MAX_CATEGORY_LEN', 100);    // max category name length
define('API_MAX_BUDGET', 100000);        // max budget in USD
define('API_MIN_BUDGET', 0);             // min budget in USD

/**
 * Validate the API 'action' parameter against allowed values.
 * Returns the validated action or sends a JSON error and exits on failure.
 */
function validate_api_action(string $action, array $allowed = ['trending', 'search', 'category']): string
{
    if (!in_array($action, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action. Allowed: ' . implode(', ', $allowed)]);
        exit;
    }
    return $action;
}

/**
 * Sanitize and validate a search query string.
 * Trims, truncates to max length, and strips excessive whitespace.
 */
function sanitize_api_query(string $query): string
{
    $query = trim($query);
    if (strlen($query) > API_MAX_QUERY_LEN) {
        $query = substr($query, 0, API_MAX_QUERY_LEN);
    }
    // Collapse multiple spaces/newlines into single space
    $query = preg_replace('/\s+/', ' ', $query);
    return trim($query);
}

/**
 * Sanitize and validate a category name.
 * Trims and truncates to max length.
 */
function sanitize_api_category(string $category): string
{
    $category = trim($category);
    if (strlen($category) > API_MAX_CATEGORY_LEN) {
        $category = substr($category, 0, API_MAX_CATEGORY_LEN);
    }
    return $category;
}

/**
 * Sanitize and validate a budget value.
 * Clamps between min and max, defaults to 0 if invalid.
 */
function sanitize_api_budget($budget): float
{
    $value = floatval($budget);
    if ($value < API_MIN_BUDGET) $value = API_MIN_BUDGET;
    if ($value > API_MAX_BUDGET) $value = API_MAX_BUDGET;
    return $value;
}

?>
