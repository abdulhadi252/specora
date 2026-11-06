<?php
/**
 * includes/loadenv.php — Specora .env Loader
 *
 * Reads the .env file from the project root and exports all variables
 * into $_ENV and putenv() so they can be accessed via getenv() or $_ENV.
 *
 * Usage: require_once __DIR__ . '/loadenv.php';
 *
 * Supports:
 *   - Comments (# and //)
 *   - Quoted values (single and double quotes)
 *   - Empty lines
 *   - Export (export KEY=val)
 */

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        // No .env file — use defaults from .env.example or system env
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) return;

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines and comments
        if ($line === '' || $line[0] === '#' || strncmp($line, '//', 2) === 0) {
            continue;
        }

        // Remove optional "export " prefix
        if (strncmp($line, 'export ', 7) === 0) {
            $line = trim(substr($line, 7));
        }

        // Split on first =
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;

        $key = trim(substr($line, 0, $eqPos));
        $val = trim(substr($line, $eqPos + 1));

        // Skip empty keys
        if ($key === '') continue;

        // Remove surrounding quotes if present
        if (strlen($val) >= 2) {
            $first = $val[0];
            $last  = $val[-1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $val = substr($val, 1, -1);
            }
        }

        // Resolve ${VAR} references in value
        $val = preg_replace_callback('/\$\{([^}]+)\}/', function ($m) {
            return getenv($m[1]) ?: $m[0];
        }, $val);

        // Set both $_ENV and putenv for maximum compatibility
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}

// Auto-load from project root
loadEnv(__DIR__ . '/../.env');

// ── Logging Directory ──
// Ensure the logs directory exists so logging works from the start
$logDir = __DIR__ . '/../uploads/logs/';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// ── PHP Error Handling Configuration ──
$appEnv = getenv('APP_ENV') ?: 'production';

// Route all PHP errors to our specora log file (in addition to display_errors settings)
$logFile = $logDir . 'specora-' . date('Y-m-d') . '.log';
ini_set('error_log', $logFile);

if ($appEnv === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('log_errors_max_len', '1024');
} else {
    // Production: log all errors but don't display them
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('log_errors_max_len', '0');
}

// ── Custom Error Handler ──
// Catches PHP errors and warnings and routes them through our specora_log system
set_error_handler(function ($severity, $message, $file, $line) {
    // Map PHP error levels to our log levels
    $level = 3; // default WARNING
    switch ($severity) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
        case E_USER_ERROR:
            $level = 5; // CRITICAL
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            $level = 4; // ERROR
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_STRICT:
            $level = 2; // INFO
            break;
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $level = 2; // INFO
            break;
    }
    
    // Strip project root from file path for readability
    $projectRoot = dirname(__DIR__);
    $shortFile = str_replace($projectRoot . '/', '', $file);
    
    // Use specora_log if available, fallback to error_log
    if (function_exists('specora_log')) {
        specora_log("PHP [{$shortFile}:{$line}] {$message}", $level);
    } else {
        error_log("PHP Error [{$shortFile}:{$line}] {$message}");
    }
    
    // Don't execute PHP's internal error handler
    return true;
});
