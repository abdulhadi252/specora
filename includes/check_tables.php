<?php
/**
 * includes/check_tables.php — Specora Shared Table Existence Checker
 *
 * Replaces the old approach of running CREATE TABLE IF NOT EXISTS
 * on every page load. This lightweight checker uses a $GLOBALS flag
 * to ensure table checks run only ONCE per request, even if multiple
 * files include this script.
 *
 * Usage: require_once 'includes/check_tables.php';
 *        check_specora_tables();
 *
 * If tables don't exist, it creates them automatically (backward compatible).
 * First run setup_tables.php for a clean initial setup.
 */

/**
 * Check and create required database tables if they don't exist.
 * Uses $GLOBALS['_specora_tables_checked'] to run only once per request.
 */
function check_specora_tables(): void
{
    // ── Request-level cache: only check once per HTTP request ──
    if (!empty($GLOBALS['_specora_tables_checked'])) {
        return;
    }
    $GLOBALS['_specora_tables_checked'] = true;

    global $connect;
    if (!isset($connect) || !$connect instanceof mysqli) {
        return; // Cannot check tables without a valid connection
    }

    // ── Create contact_messages table if missing ──
    $cmCheck = $connect->query("SHOW TABLES LIKE 'contact_messages'");
    if ($cmCheck && $cmCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) DEFAULT '',
                category VARCHAR(100) DEFAULT '',
                message TEXT NOT NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                ip_address VARCHAR(45) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL DEFAULT NULL,
                approved_at TIMESTAMP NULL DEFAULT NULL
            )
        ");
    } else {
        // ── Ensure approved_at column exists for approval workflow ──
        $aaCheck = $connect->query("SHOW COLUMNS FROM contact_messages LIKE 'approved_at'");
        if ($aaCheck && $aaCheck->num_rows === 0) {
            $connect->query("ALTER TABLE contact_messages ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL AFTER read_at");
        }
        
        // ── Auto-repair missing AUTO_INCREMENT on id column ──
        // Some table creation paths could leave the id column without AUTO_INCREMENT,
        // causing "Duplicate entry '0' for key 'PRIMARY'" errors on insert.
        $aiCheck = $connect->query("SHOW COLUMNS FROM contact_messages LIKE 'id'");
        if ($aiCheck && $col = $aiCheck->fetch_assoc()) {
            if (stripos($col['Extra'], 'auto_increment') === false) {
                // Remove any rows with id=0 or NULL before altering
                $connect->query("DELETE FROM contact_messages WHERE id IS NULL OR id = 0");
                $connect->query("ALTER TABLE contact_messages MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY");
            }
        }
        // ── Migrate existing contact_messages status to new approval system ──
        $colCheck = $connect->query("SHOW COLUMNS FROM contact_messages LIKE 'status'");
        if ($colCheck && $colRow = $colCheck->fetch_assoc()) {
            $currentType = strtolower($colRow['Type']);
            if (strpos($currentType, 'pending') === false) {
                // Update old statuses: unread→pending, read→approved, replied→approved
                $connect->query("UPDATE contact_messages SET status = 'pending' WHERE status IN ('unread','read','replied')");
                $connect->query("ALTER TABLE contact_messages MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
            }
        }
    }

    // ── Create about_page_settings table if missing ──
    $apsCheck = $connect->query("SHOW TABLES LIKE 'about_page_settings'");
    if ($apsCheck && $apsCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS about_page_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_key VARCHAR(100) NOT NULL UNIQUE,
                section_label VARCHAR(255) NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                content LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    // ── Create testimonials table if missing ──
    $tmCheck = $connect->query("SHOW TABLES LIKE 'testimonials'");
    if ($tmCheck && $tmCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) DEFAULT '',
                city VARCHAR(100) DEFAULT '',
                feedback TEXT NOT NULL,
                status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }

    // ── Create newsletter_subscribers table if missing ──
    $nlCheck = $connect->query("SHOW TABLES LIKE 'newsletter_subscribers'");
    if ($nlCheck && $nlCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                ip_address VARCHAR(45) DEFAULT '',
                status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    // ═══════════════════════════════════════════════════════
    // VISITOR ANALYTICS TABLES
    // ═══════════════════════════════════════════════════════

    // ── visitor_sessions table ──
    $vsCheck = $connect->query("SHOW TABLES LIKE 'visitor_sessions'");
    if ($vsCheck && $vsCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS visitor_sessions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(64) NOT NULL UNIQUE,
                ip_address VARCHAR(45) NOT NULL DEFAULT '',
                country VARCHAR(100) DEFAULT '',
                city VARCHAR(100) DEFAULT '',
                browser VARCHAR(50) DEFAULT '',
                browser_version VARCHAR(20) DEFAULT '',
                os VARCHAR(50) DEFAULT '',
                device_type ENUM('desktop','mobile','tablet','unknown') DEFAULT 'unknown',
                screen_resolution VARCHAR(20) DEFAULT '',
                referrer VARCHAR(500) DEFAULT '',
                referrer_source VARCHAR(50) DEFAULT 'Direct',
                entry_page VARCHAR(500) DEFAULT '',
                exit_page VARCHAR(500) DEFAULT '',
                visitor_type ENUM('new','returning') DEFAULT 'new',
                session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                session_duration INT UNSIGNED DEFAULT 0,
                is_online TINYINT(1) DEFAULT 1,
                total_pages_viewed INT UNSIGNED DEFAULT 0,
                INDEX idx_session_id (session_id),
                INDEX idx_ip (ip_address),
                INDEX idx_last_activity (last_activity),
                INDEX idx_visitor_type (visitor_type),
                INDEX idx_device_type (device_type),
                INDEX idx_browser (browser),
                INDEX idx_os (os),
                INDEX idx_country (country),
                INDEX idx_referrer_source (referrer_source),
                INDEX idx_session_start (session_start)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ── page_views table ──
    $pvCheck = $connect->query("SHOW TABLES LIKE 'page_views'");
    if ($pvCheck && $pvCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS page_views (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(64) NOT NULL,
                page_url VARCHAR(500) NOT NULL,
                page_title VARCHAR(255) DEFAULT '',
                time_spent INT UNSIGNED DEFAULT 0,
                is_unique TINYINT(1) DEFAULT 0,
                viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_session_id (session_id),
                INDEX idx_page_url (page_url),
                INDEX idx_viewed_at (viewed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ── daily_stats table ──
    $dsCheck = $connect->query("SHOW TABLES LIKE 'daily_stats'");
    if ($dsCheck && $dsCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS daily_stats (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                stat_date DATE NOT NULL UNIQUE,
                total_visitors INT UNSIGNED DEFAULT 0,
                total_views INT UNSIGNED DEFAULT 0,
                unique_visitors INT UNSIGNED DEFAULT 0,
                new_visitors INT UNSIGNED DEFAULT 0,
                returning_visitors INT UNSIGNED DEFAULT 0,
                desktop_count INT UNSIGNED DEFAULT 0,
                mobile_count INT UNSIGNED DEFAULT 0,
                tablet_count INT UNSIGNED DEFAULT 0,
                avg_session_duration DECIMAL(10,2) DEFAULT 0,
                INDEX idx_stat_date (stat_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    // ── geo_cache table (for caching geolocation lookups) ──
    $gcCheck = $connect->query("SHOW TABLES LIKE 'geo_cache'");
    if ($gcCheck && $gcCheck->num_rows === 0) {
        $connect->query("
            CREATE TABLE IF NOT EXISTS geo_cache (
                ip_address VARCHAR(45) PRIMARY KEY,
                country VARCHAR(100) DEFAULT '',
                city VARCHAR(100) DEFAULT '',
                cached_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ip (ip_address)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }
}
