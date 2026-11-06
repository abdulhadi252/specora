<?php
/**
 * includes/visitor_tracker.php — Specora Visitor Tracking Engine
 *
 * Server-side visitor tracking include. Provides helper functions
 * for analytics queries used by the admin dashboard.
 *
 * This file does NOT modify the frontend. It only provides:
 *   - Tracking helper functions
 *   - Analytics query functions
 *   - Session cleanup for online visitors
 *
 * Frontend tracking is done via js/visitor-tracker.js → api/track.php
 */

// ── If this file is included directly, ensure tables exist ──
if (!empty($GLOBALS['_specora_tables_checked']) && function_exists('check_specora_tables')) {
    // Tables already checked in this request
}

/**
 * Get the total number of visitors.
 */
function getTotalVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get total unique visitors (by unique IP).
 */
function getTotalUniqueVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(DISTINCT ip_address) as c FROM visitor_sessions WHERE ip_address != ''");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get today's visitors.
 */
function getTodayVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE DATE(session_start) = CURDATE()");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get yesterday's visitors.
 */
function getYesterdayVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE DATE(session_start) = CURDATE() - INTERVAL 1 DAY");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get this week's visitors (Monday to today).
 */
function getThisWeekVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE YEARWEEK(session_start) = YEARWEEK(CURDATE())");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get this month's visitors.
 */
function getThisMonthVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE MONTH(session_start) = MONTH(CURDATE()) AND YEAR(session_start) = YEAR(CURDATE())");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get total page views.
 */
function getTotalPageViews(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM page_views");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get unique page views (one per session per page).
 */
function getUniquePageViews(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM page_views WHERE is_unique = 1");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get currently online visitors (active in last 2 minutes).
 */
function getOnlineVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE is_online = 1 AND last_activity >= NOW() - INTERVAL 2 MINUTE");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get new visitors count.
 */
function getNewVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE visitor_type = 'new'");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get returning visitors count.
 */
function getReturningVisitors(mysqli $connect): int
{
    $r = $connect->query("SELECT COUNT(*) as c FROM visitor_sessions WHERE visitor_type = 'returning'");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}

/**
 * Get average session duration in seconds.
 */
function getAverageSessionDuration(mysqli $connect): float
{
    $r = $connect->query("SELECT COALESCE(AVG(session_duration), 0) as avg_dur FROM visitor_sessions WHERE session_duration > 0");
    return $r ? (float)$r->fetch_assoc()['avg_dur'] : 0;
}

/**
 * Get top pages by views.
 */
function getTopPages(mysqli $connect, int $limit = 10): array
{
    $pages = [];
    $r = $connect->query("
        SELECT page_url, COUNT(*) as views, 
               COUNT(DISTINCT session_id) as unique_views,
               COALESCE(AVG(time_spent), 0) as avg_time
        FROM page_views 
        GROUP BY page_url 
        ORDER BY views DESC 
        LIMIT $limit
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $pages[] = $row;
        }
    }
    return $pages;
}

/**
 * Get page with highest views.
 */
function getMostViewedPage(mysqli $connect): array
{
    $pages = getTopPages($connect, 1);
    return $pages[0] ?? ['page_url' => '', 'views' => 0, 'unique_views' => 0, 'avg_time' => 0];
}

/**
 * Get page with highest average time spent.
 */
function getMostEngagingPage(mysqli $connect): array
{
    $r = $connect->query("
        SELECT page_url, COUNT(*) as views, 
               COUNT(DISTINCT session_id) as unique_views,
               COALESCE(AVG(time_spent), 0) as avg_time
        FROM page_views 
        WHERE time_spent > 0
        GROUP BY page_url 
        HAVING views > 1
        ORDER BY avg_time DESC 
        LIMIT 1
    ");
    if ($r && $r->num_rows > 0) {
        return $r->fetch_assoc();
    }
    return ['page_url' => '', 'views' => 0, 'unique_views' => 0, 'avg_time' => 0];
}

/**
 * Get visitors by device type.
 */
function getDeviceDistribution(mysqli $connect): array
{
    $devices = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
    $r = $connect->query("
        SELECT device_type, COUNT(*) as count 
        FROM visitor_sessions 
        GROUP BY device_type
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $devices[$row['device_type']] = (int)$row['count'];
        }
    }
    return $devices;
}

/**
 * Get visitors by browser.
 */
function getBrowserDistribution(mysqli $connect): array
{
    $browsers = [];
    $r = $connect->query("
        SELECT browser, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE browser != '' 
        GROUP BY browser 
        ORDER BY count DESC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $browsers[$row['browser']] = (int)$row['count'];
        }
    }
    return $browsers;
}

/**
 * Get visitors by operating system.
 */
function getOsDistribution(mysqli $connect): array
{
    $oss = [];
    $r = $connect->query("
        SELECT os, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE os != '' 
        GROUP BY os 
        ORDER BY count DESC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $oss[$row['os']] = (int)$row['count'];
        }
    }
    return $oss;
}

/**
 * Get visitors by country.
 */
function getCountryDistribution(mysqli $connect): array
{
    $countries = [];
    $r = $connect->query("
        SELECT country, COUNT(*) as count 
        FROM visitor_sessions 
        WHERE country != '' 
        GROUP BY country 
        ORDER BY count DESC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $countries[$row['country']] = (int)$row['count'];
        }
    }
    return $countries;
}

/**
 * Get traffic sources distribution.
 */
function getTrafficSources(mysqli $connect): array
{
    $sources = [];
    $r = $connect->query("
        SELECT referrer_source, COUNT(*) as count 
        FROM visitor_sessions 
        GROUP BY referrer_source 
        ORDER BY count DESC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $sources[$row['referrer_source']] = (int)$row['count'];
        }
    }
    return $sources;
}

/**
 * Get visitors by day for the last N days.
 */
function getVisitorsByDay(mysqli $connect, int $days = 30): array
{
    $data = [];
    $r = $connect->query("
        SELECT DATE(session_start) as date, COUNT(*) as visitors,
               COUNT(DISTINCT ip_address) as unique_visitors
        FROM visitor_sessions
        WHERE session_start >= CURDATE() - INTERVAL $days DAY
        GROUP BY DATE(session_start)
        ORDER BY date ASC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Get visitors by week for the last 12 weeks.
 */
function getVisitorsByWeek(mysqli $connect): array
{
    $data = [];
    $r = $connect->query("
        SELECT YEARWEEK(session_start) as yearweek,
               MIN(DATE(session_start)) as week_start,
               COUNT(*) as visitors
        FROM visitor_sessions
        WHERE session_start >= CURDATE() - INTERVAL 12 WEEK
        GROUP BY YEARWEEK(session_start)
        ORDER BY yearweek ASC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Get visitors by month for the last 12 months.
 */
function getVisitorsByMonth(mysqli $connect): array
{
    $data = [];
    $r = $connect->query("
        SELECT YEAR(session_start) as year, MONTH(session_start) as month,
               DATE_FORMAT(session_start, '%Y-%m') as yearmonth,
               COUNT(*) as visitors
        FROM visitor_sessions
        WHERE session_start >= CURDATE() - INTERVAL 12 MONTH
        GROUP BY YEAR(session_start), MONTH(session_start)
        ORDER BY year ASC, month ASC
    ");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

/**
 * Get all visitors with details (for the visitor table).
 */
function getVisitorsDetailed(mysqli $connect, string $search = '', string $sortBy = 'session_start', string $sortOrder = 'DESC', int $limit = 50, int $offset = 0, string $dateFrom = '', string $dateTo = ''): array
{
    $where = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $searchTerm = '%' . $search . '%';
        $where[] = "(session_id LIKE ? OR ip_address LIKE ? OR country LIKE ? OR city LIKE ? OR browser LIKE ? OR os LIKE ? OR referrer_source LIKE ?)";
        $params = array_fill(0, 7, $searchTerm);
        $types = 'sssssss';
    }

    if ($dateFrom !== '') {
        $where[] = "DATE(session_start) >= ?";
        $params[] = $dateFrom;
        $types .= 's';
    }

    if ($dateTo !== '') {
        $where[] = "DATE(session_start) <= ?";
        $params[] = $dateTo;
        $types .= 's';
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Whitelist sort columns
    $allowedSort = ['session_id', 'ip_address', 'country', 'city', 'browser', 'os', 'device_type', 'session_start', 'last_activity', 'session_duration', 'total_pages_viewed', 'visitor_type', 'referrer_source'];
    if (!in_array($sortBy, $allowedSort)) $sortBy = 'session_start';
    if ($sortOrder !== 'ASC') $sortOrder = 'DESC';

    $sql = "SELECT * FROM visitor_sessions $whereClause ORDER BY $sortBy $sortOrder LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $visitors = [];
    $stmt = $connect->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $visitors[] = $row;
        }
        $stmt->close();
    }

    return $visitors;
}

/**
 * Get total count of visitors matching filters.
 */
function getVisitorsCount(mysqli $connect, string $search = '', string $dateFrom = '', string $dateTo = ''): int
{
    $where = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $searchTerm = '%' . $search . '%';
        $where[] = "(session_id LIKE ? OR ip_address LIKE ? OR country LIKE ? OR city LIKE ? OR browser LIKE ? OR os LIKE ? OR referrer_source LIKE ?)";
        $params = array_fill(0, 7, $searchTerm);
        $types = 'sssssss';
    }

    if ($dateFrom !== '') {
        $where[] = "DATE(session_start) >= ?";
        $params[] = $dateFrom;
        $types .= 's';
    }

    if ($dateTo !== '') {
        $where[] = "DATE(session_start) <= ?";
        $params[] = $dateTo;
        $types .= 's';
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT COUNT(*) as c FROM visitor_sessions $whereClause";

    $stmt = $connect->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int)$row['c'];
    }

    return 0;
}

/**
 * Clean up stale online sessions (mark as offline after 5 minutes of inactivity).
 * Call this periodically or on dashboard load.
 */
function cleanupStaleSessions(mysqli $connect): void
{
    $connect->query("
        UPDATE visitor_sessions SET 
            is_online = 0,
            session_duration = TIMESTAMPDIFF(SECOND, session_start, last_activity)
        WHERE is_online = 1 AND last_activity < NOW() - INTERVAL 5 MINUTE
    ");
}

/**
 * Format seconds into human-readable time.
 */
function formatDuration(int $seconds): string
{
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return $hours . 'h ' . $minutes . 'm';
}

/**
 * Get visitor session by ID (for the exit page update).
 */
function getSessionBySessionId(mysqli $connect, string $sessionId): ?array
{
    $stmt = $connect->prepare("SELECT * FROM visitor_sessions WHERE session_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        }
        $stmt->close();
    }
    return null;
}
