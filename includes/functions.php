<?php
/**
 * includes/functions.php — Specora Shared Helper Functions
 */

/**
 * Centralized logging function — use this instead of raw error_log().
 *
 * Writes structured log entries with timestamps, log levels, and
 * automatic caller file/line context to a date-rotated log file.
 * Log level can be controlled via the LOG_LEVEL env variable.
 *
 * Log levels (severity ascending):
 *   1 = DEBUG     — Detailed debug information
 *   2 = INFO      — General operational messages
 *   3 = WARNING   — Non-critical issues that need attention
 *   4 = ERROR     — Runtime errors that should be monitored
 *   5 = CRITICAL  — System is unusable, action required immediately
 *
 * @param string $message The log message
 * @param int    $level   Log level (1-5). Default 3 (WARNING)
 */
function specora_log(string $message, int $level = 3): void
{
    // Check if this level should be logged based on configured threshold
    static $logLevel = null;
    if ($logLevel === null) {
        $envLevel = getenv('LOG_LEVEL') ?: 'WARNING';
        $map = ['DEBUG' => 1, 'INFO' => 2, 'WARNING' => 3, 'ERROR' => 4, 'CRITICAL' => 5];
        $logLevel = $map[strtoupper($envLevel)] ?? 3;
    }

    if ($level < $logLevel) return;

    $labels = [1 => 'DEBUG', 2 => 'INFO', 3 => 'WARNING', 4 => 'ERROR', 5 => 'CRITICAL'];
    $label = $labels[$level] ?? 'LOG';

    // Auto-detect caller file and line
    $caller = '';
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    if (isset($trace[1])) {
        $callerFile = $trace[1]['file'] ?? '';
        $callerLine = $trace[1]['line'] ?? 0;
        if ($callerFile) {
            // Strip project root path for readability
            $callerFile = str_replace(dirname(__DIR__) . '/', '', $callerFile);
            $caller = " [{$callerFile}:{$callerLine}]";
        }
    }

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$label}{$caller}: {$message}" . PHP_EOL;

    // Write to log file with date-based rotation
    $logDir = __DIR__ . '/../uploads/logs/';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . 'specora-' . date('Y-m-d') . '.log';

    // Rotate if file exceeds 10MB
    if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
        $rotated = $logFile . '.old';
        @rename($logFile, $rotated);
    }

    $written = @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

    // Fallback to system error_log if file write fails
    if ($written === false) {
        error_log("specora_log failed: {$message}");
    }
}

/**
 * Validate database connection before using $connect.
 *
 * Checks if $connect is a valid MySQLi instance and the connection
 * is still alive. Call this before any $connect->query() or
 * $connect->prepare() call in files that don't already have checks.
 *
 * @param  mysqli|null $connect The database connection variable
 * @return bool True if connection is valid and alive
 */
function validate_db_connection($connect): bool
{
    // Check if $connect is a valid MySQLi instance without errors
    if ($connect instanceof mysqli && !$connect->connect_error) {
        try {
            // Lightweight ping to verify connection is still alive
            return $connect->ping();
        } catch (mysqli_sql_exception $e) {
            specora_log('Database ping failed: ' . $e->getMessage(), 4); // ERROR level
            return false;
        }
    }
    
    return false;
}

/**
 * Generate product thumbnail image HTML with fallback
 */
function thumbImg(string $photo, string $alt = ''): string
{
    $src = $photo ?: 'https://placehold.co/120x120/F4F3F0/A8A49C?text=...';
    return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '" loading="lazy" onerror="this.src=\'https://placehold.co/120x120/F4F3F0/A8A49C?text=No+Img\'" />';
}

/**
 * Fetch category groups from the admin_category table
 * Each row = 1 main category + 4 sub-categories
 */
function fetchCategoryGroupsFromDb($connect): array
{
    $groups = [];
    
    $gRes = mysqli_query($connect, "SELECT * FROM admin_category ORDER BY sort_order ASC, created_at ASC");
    if (!$gRes) return [];
    
    while ($g = mysqli_fetch_assoc($gRes)) {
        $gKey = $g['category_slug'];
        $groups[$gKey] = [
            'label' => $g['category_name'],
            'icon'  => '📁',
            'color' => '#6366F1',
            'subs'  => [],
        ];
        // Build subs from sub1-sub4 columns
        for ($si = 1; $si <= 4; $si++) {
            $sName = $g["sub{$si}_name"] ?? '';
            $sSlug = $g["sub{$si}_slug"] ?? '';
            if (!empty($sName) && !empty($sSlug)) {
                $groups[$gKey]['subs'][$sSlug] = [
                    'label' => $sName,
                    'icon'  => '🔖',
                ];
            }
        }
    }
    
    return $groups;
}

/**
 * Fetch the full category tree from admin_category table.
 * Uses a single query — no N+1.
 */
function fetchCategoryTree($connect): array
{
    $tree = [];
    
    // Single query: fetch all category data including sub1-sub4 columns
    $gRes = mysqli_query($connect, "
        SELECT category_slug, category_name, sort_order,
               sub1_name, sub1_slug, sub2_name, sub2_slug,
               sub3_name, sub3_slug, sub4_name, sub4_slug
        FROM admin_category
        ORDER BY sort_order ASC, created_at ASC
    ");
    if (!$gRes) return [];
    
    while ($g = mysqli_fetch_assoc($gRes)) {
        $node = [
            'slug'     => $g['category_slug'],
            'name'     => $g['category_name'],
            'icon'     => '📁',
            'children' => [],
        ];
        // Build children from sub1-sub4 columns
        for ($si = 1; $si <= 4; $si++) {
            $sName = $g["sub{$si}_name"] ?? '';
            $sSlug = $g["sub{$si}_slug"] ?? '';
            if (!empty($sName) && !empty($sSlug)) {
                $node['children'][] = [
                    'slug'     => $sSlug,
                    'name'     => $sName,
                    'icon'     => '🔖',
                    'children' => [],
                ];
            }
        }
        $tree[] = $node;
    }
    
    return $tree;
}

/**
 * Fetch one representative product photo per category
 */
function fetchGroupPhotos($connect): array
{
    $photos = [];
    $sql = "SELECT ac.category_slug, 
                   (SELECT MIN(p.photo) FROM products p WHERE p.search_query LIKE CONCAT('%', ac.category_name, '%') AND p.photo IS NOT NULL AND p.photo != '' LIMIT 1) as photo
            FROM admin_category ac";
    $res = mysqli_query($connect, $sql);
    if (!$res) return [];
    while ($row = mysqli_fetch_assoc($res)) {
        $photos[$row['category_slug']] = $row['photo'];
    }
    return $photos;
}

/**
 * Fetch a category image from product tables for the navbar.
 *
 * @param  mysqli|null $connect  Database connection
 * @param  string      $cat_name Category name to search for
 * @return string Image URL or empty string if not found
 */
function getNavCategoryImage($connect, $cat_name): string
{
    if (!isset($connect) || !$connect) return '';
    $st = mysqli_real_escape_string($connect, $cat_name);
    if ($st === '') return '';
    $tables = array('aliexpress_products', 'products', 'walmart_products', 'ebay_products');
    foreach ($tables as $table) {
        $check = @mysqli_query($connect, "SHOW TABLES LIKE '$table'");
        if (!$check || mysqli_num_rows($check) == 0) continue;
        $q = "SELECT photo FROM $table WHERE search_query LIKE '%$st%' AND photo != '' AND photo NOT LIKE '%placehold%' AND photo IS NOT NULL LIMIT 1";
        $r = @mysqli_query($connect, $q);
        if ($r && $row = @mysqli_fetch_assoc($r)) return $row['photo'];
    }
    return '';
}

/**
 * Format a database row into a standardized API product response.
 *
 * Centralizes the response format so internal DB column names are NOT
 * exposed in API JSON. All 4 API endpoint files call this function
 * instead of building their own response arrays inline.
 *
 * @param array  $row     Database row (aliexpress_products, products, ebay_products, etc.)
 * @param string $source  One of: 'aliexpress', 'amazon', 'ebay', 'walmart'
 * @param array  $overrides Optional overrides for source-specific values
 * @return array Standardized product array for JSON response
 */
function format_product_response(array $row, string $source, array $overrides = []): array
{
    // Map the ID field based on source (different tables use different column names)
    $idField = '';
    switch ($source) {
        case 'aliexpress': $idField = $row['product_id'] ?? ''; break;
        case 'amazon':     $idField = $row['asin'] ?? '';       break;
        case 'ebay':       $idField = $row['item_id'] ?? '';    break;
        case 'walmart':    $idField = $row['item_id'] ?? '';    break;
        default:           $idField = $row['id'] ?? '';         break;
    }

    return [
        'asin'                => $idField,
        'source'              => $source,
        'product_title'       => $row['title'] ?? '',
        'product_brand'       => $overrides['brand'] ?? ($row['brand'] ?? ''),
        'category'            => $row['category_name'] ?? '',
        'category_id'         => $row['category_id'] ?? 0,
        'product_url'         => $row['affiliate_link'] ?? '',
        'product_photo'       => $row['photo'] ?? '',
        'product_price'       => isset($row['price_usd']) ? '$' . number_format((float)$row['price_usd'], 2) : 'N/A',
        'product_star_rating' => $row['rating'] ?? null,
        'product_num_ratings' => $row['num_ratings'] ?? 0,
        'is_prime'            => (bool)($overrides['is_prime'] ?? ($row['is_prime'] ?? false)),
        'is_best_seller'      => (bool)($overrides['is_best_seller'] ?? ($row['is_best_seller'] ?? false)),
        'is_amazon_choice'    => (bool)($overrides['is_amazon_choice'] ?? ($row['is_choice'] ?? false)),
        'badge_label'         => $overrides['badge_label'] ?? ($row['badge'] ?? null),
    ];
}
