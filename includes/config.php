<?php
/**
 * includes/config.php — Specora Shared Configuration
 * 
 * Categories dynamically fetched from database (category_groups + category_subgroups tables).
 * Manage from the admin panel — add, remove, or edit categories.
 */

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/functions.php';

// Validate database connection — halt gracefully if DB is down
if (!validate_db_connection($connect)) {
    specora_log('Database connection failed in config.php', 5); // CRITICAL
    die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try again later.</p>');
}

// ── Load .env file for environment variables (SMTP, API keys, etc.) ──
require_once __DIR__ . '/loadenv.php';

/**
 * CATEGORIES — Dynamically from database
 * 
 * Structure returned:
 *   [group_key => ['label' => ..., 'icon' => ..., 'color' => ..., 'subs' => [sub_key => ['label' => ..., 'icon' => ...]]]]
 */
$categoryGroups = fetchCategoryGroupsFromDb($connect);
