<?php
/**
 * includes/connect.php — Specora Database Connection
 * 
 * Establishes a MySQLi connection. If the connection fails,
 * sets $connect to null so callers can gracefully check
 * before using it with validate_db_connection().
 *
 * NOTE: specora_log() is NOT available yet (defined in functions.php,
 * which is loaded AFTER this file). Use a helper closure so that
 * logging works whether or not functions.php has been loaded.
 */

// Load .env file for DB credentials and other environment variables
require_once __DIR__ . '/loadenv.php';

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'specora';

// Helper: use specora_log() if available, otherwise fall back to error_log()
$log = function ($msg, $level = 5) {
    if (function_exists('specora_log')) {
        specora_log($msg, $level);
    } else {
        error_log('[CRITICAL] ' . $msg);
    }
};

try {
    $connect = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    if (!$connect) {
        $connect = null;
        $log('Database connection failed: mysqli_connect returned false');
    }
} catch (mysqli_sql_exception $e) {
    $connect = null;
    $log('Database connection failed: ' . $e->getMessage());
}
?>
