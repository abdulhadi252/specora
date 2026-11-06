<?php
/**
 * admin/analytics.php — Specora Visitor Analytics Dashboard
 *
 * Complete analytics dashboard showing:
 *   - Dashboard cards with key metrics
 *   - Charts (visitors by day/week/month, device, browser, OS, traffic, countries)
 *   - Visitors table with search, pagination, sorting, date filters
 *   - Top pages report
 *   - Device / Browser / OS breakdowns 
 *   - Online visitors
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/visitor_tracker.php';
require_once __DIR__ . '/../includes/check_tables.php';

require_admin_login();

// Ensure analytics tables exist before querying them
check_specora_tables();

// Clean up stale sessions on dashboard load
cleanupStaleSessions($connect);

// ── Pagination & filters ──
$page       = max(1, (int)($_GET['p'] ?? 1));
$perPage    = 50;
$offset     = ($page - 1) * $perPage;
$search     = trim($_GET['search'] ?? '');
$sortBy     = trim($_GET['sort'] ?? 'session_start');
$sortOrder  = trim($_GET['order'] ?? 'DESC');
$dateFrom   = trim($_GET['date_from'] ?? '');
$dateTo     = trim($_GET['date_to'] ?? '');

// ── Fetch aggregated data using helpers from visitor_tracker.php ──
$totalVisitors     = getTotalVisitors($connect);
$uniqueVisitors    = getTotalUniqueVisitors($connect);
$todayVisitors     = getTodayVisitors($connect);
$yesterdayVisitors = getYesterdayVisitors($connect);
$thisWeekVisitors  = getThisWeekVisitors($connect);
$thisMonthVisitors = getThisMonthVisitors($connect);
$onlineVisitors    = getOnlineVisitors($connect);
$totalPageViews    = getTotalPageViews($connect);
$newVisitors       = getNewVisitors($connect);
$returningVisitors = getReturningVisitors($connect);
$avgSessionDur     = getAverageSessionDuration($connect);
$topPages          = getTopPages($connect, 10);
$deviceDist        = getDeviceDistribution($connect);
$browserDist       = getBrowserDistribution($connect);
$osDist            = getOsDistribution($connect);
$countryDist       = getCountryDistribution($connect);
$trafficSources    = getTrafficSources($connect);
$visitorsByDay     = getVisitorsByDay($connect, 30);
$visitorsByWeek    = getVisitorsByWeek($connect);
$visitorsByMonth   = getVisitorsByMonth($connect);
$mostViewedPage    = getMostViewedPage($connect);
$mostEngagingPage  = getMostEngagingPage($connect);

// ── Fetch detailed visitors for table ──
$totalFiltered = getVisitorsCount($connect, $search, $dateFrom, $dateTo);
$totalPages    = max(1, ceil($totalFiltered / $perPage));
$visitors      = getVisitorsDetailed($connect, $search, $sortBy, $sortOrder, $perPage, $offset, $dateFrom, $dateTo);

$currentPage = 'analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Analytics — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}
img{max-width:100%;display:block;}

.main{padding:40px 48px;flex:1;max-width:1400px;margin:0 auto;width:100%;}
.page-header{margin-bottom:28px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:4px;}
.page-sub{font-size:14px;color:var(--gray-600);}

/* ── STATS CARDS ── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:32px;}
.stat-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:20px;transition:transform .2s,box-shadow .2s;position:relative;overflow:hidden;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.06);}
.stat-card .stat-accent{position:absolute;top:0;left:0;right:0;height:3px;}
.stat-card .stat-label{font-size:11px;color:var(--gray-400);font-weight:600;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;}
.stat-card .stat-value{font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--black);letter-spacing:-.5px;}
.stat-card .stat-sub{font-size:12px;color:var(--gray-400);margin-top:4px;}
.bg-gold{background:var(--gold);}.bg-green{background:#10B981;}.bg-blue{background:#3B82F6;}.bg-purple{background:#8B5CF6;}.bg-red{background:#EF4444;}.bg-orange{background:#F59E0B;}

/* ── SECTION TITLES ── */
.section-title{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--black);margin-bottom:16px;display:flex;align-items:center;gap:10px;}
.section-title .badge{font-size:11px;font-weight:600;padding:2px 10px;border-radius:var(--radius-pill);background:var(--gray-100);color:var(--gray-600);}

/* ── CARDS ── */
.card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:24px;box-shadow:0 4px 16px rgba(0,0,0,.04);margin-bottom:20px;}
.card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;}
.card-title{font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--black);}

/* ── CHARTS ── */
.charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:32px;}
.chart-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:20px;}
.chart-card h4{font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);margin-bottom:14px;}
.chart-wrap{position:relative;height:260px;}
.chart-wrap canvas{max-width:100%;max-height:260px;}

/* ── SECTION HEADER ── */
.section-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;}

/* ── FILTERS ── */
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filters input,.filters select{font-family:var(--font-body);font-size:13px;padding:8px 14px;border:1.5px solid var(--gray-200);border-radius:var(--radius-sm);outline:none;background:var(--white);transition:border-color .2s;color:var(--gray-600);}
.filters input:focus,.filters select:focus{border-color:var(--gold);}
.filters .btn{font-size:12px;font-weight:600;padding:8px 18px;border-radius:var(--radius-pill);border:none;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:6px;}
.btn-gold{background:var(--gold);color:var(--black);}
.btn-gold:hover{background:var(--gold-dark);color:var(--white);}
.btn-outline{background:var(--white);border:1.5px solid var(--gray-200);color:var(--gray-600);}
.btn-outline:hover{border-color:var(--gray-400);color:var(--black);}

/* ── TABLE ── */
.table-wrap{overflow-x:auto;border:1px solid var(--gray-200);border-radius:var(--radius);}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{text-align:left;font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--gray-400);padding:12px 14px;border-bottom:1px solid var(--gray-200);background:var(--off-white);white-space:nowrap;}
th a{color:var(--gray-400);text-decoration:none;display:flex;align-items:center;gap:4px;}
th a:hover{color:var(--gold-dark);}
td{padding:10px 14px;border-bottom:1px solid var(--gray-100);color:var(--gray-600);vertical-align:middle;}
tr:hover td{background:var(--off-white);}
td .badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:var(--radius-pill);}
.badge-new{background:rgba(16,185,129,.1);color:#065F46;}
.badge-return{background:rgba(59,130,246,.1);color:#1E40AF;}
.badge-online{background:rgba(16,185,129,.12);color:#065F46;font-size:10px;padding:2px 8px;border-radius:var(--radius-pill);font-weight:600;}
.badge-offline{background:var(--gray-100);color:var(--gray-400);font-size:10px;padding:2px 8px;border-radius:var(--radius-pill);font-weight:600;}

/* ── PAGINATION ── */
.pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;}
.pagination a,.pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;font-size:13px;font-weight:600;border-radius:var(--radius-sm);transition:all .2s;}
.pagination a{background:var(--white);border:1px solid var(--gray-200);color:var(--gray-600);text-decoration:none;}
.pagination a:hover{border-color:var(--gold);color:var(--black);}
.pagination .active{background:var(--gold);border-color:var(--gold);color:var(--black);}

/* ── RESPONSIVE ── */
@media(max-width:1200px){.charts-grid{grid-template-columns:1fr;}}
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .stats-grid{grid-template-columns:repeat(3,1fr);}
}
@media(max-width:768px){
  .main{padding:24px 16px;}
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .filters{flex-direction:column;align-items:stretch;}
  .card{padding:16px;}
}
footer{background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);}
footer span{color:var(--gold);}
@media(max-width:768px){footer{padding:16px 20px;}}
</style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="sidebar-page">
<div class="main">

  <!-- HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title">Visitor Analytics</h1>
    <p class="page-sub">Real-time visitor tracking and analytics for Specora.</p>
  </div>

  <!-- ══ STATS CARDS ══ -->
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-accent bg-gold"></div><div class="stat-label">Total Visitors</div><div class="stat-value"><?= number_format($totalVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-green"></div><div class="stat-label">Online Now</div><div class="stat-value"><?= $onlineVisitors ?></div><div class="stat-sub">Active in last 2 min</div></div>
    <div class="stat-card"><div class="stat-accent bg-blue"></div><div class="stat-label">Today</div><div class="stat-value"><?= number_format($todayVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-purple"></div><div class="stat-label">Yesterday</div><div class="stat-value"><?= number_format($yesterdayVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-orange"></div><div class="stat-label">This Week</div><div class="stat-value"><?= number_format($thisWeekVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-red"></div><div class="stat-label">This Month</div><div class="stat-value"><?= number_format($thisMonthVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-green"></div><div class="stat-label">New Visitors</div><div class="stat-value"><?= number_format($newVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-blue"></div><div class="stat-label">Returning</div><div class="stat-value"><?= number_format($returningVisitors) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-purple"></div><div class="stat-label">Avg Session</div><div class="stat-value"><?= formatDuration((int)$avgSessionDur) ?></div></div>
    <div class="stat-card"><div class="stat-accent bg-gold"></div><div class="stat-label">Page Views</div><div class="stat-value"><?= number_format($totalPageViews) ?></div></div>
  </div>

  <!-- ══ TOP INSIGHTS ══ -->
  <?php if ($mostViewedPage['page_url']): ?>
  <div class="card" style="margin-bottom:20px;background:var(--off-white);border-color:var(--gold-light);">
    <div style="display:flex;flex-wrap:wrap;gap:24px;align-items:center;">
      <div><span style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Most Viewed Page</span><br><span style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--black);word-break:break-all;"><?= htmlspecialchars($mostViewedPage['page_url']) ?></span><br><span style="font-size:13px;color:var(--gold-dark);font-weight:600;"><?= number_format($mostViewedPage['views']) ?> views</span></div>
      <?php if ($mostEngagingPage['page_url']): ?>
      <div><span style="font-size:11px;color:var(--gray-400);font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Most Engaging Page</span><br><span style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--black);word-break:break-all;"><?= htmlspecialchars($mostEngagingPage['page_url']) ?></span><br><span style="font-size:13px;color:var(--gold-dark);font-weight:600;">Avg <?= formatDuration((int)$mostEngagingPage['avg_time']) ?></span></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- ══ CHARTS ══ -->
  <div class="charts-grid">
    <div class="chart-card">
      <h4>Visitors (Last 30 Days)</h4>
      <div class="chart-wrap"><canvas id="chartDaily"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Visitors by Week (Last 12 Weeks)</h4>
      <div class="chart-wrap"><canvas id="chartWeekly"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Visitors by Month (Last 12 Months)</h4>
      <div class="chart-wrap"><canvas id="chartMonthly"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Device Distribution</h4>
      <div class="chart-wrap"><canvas id="chartDevice"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Browser Distribution</h4>
      <div class="chart-wrap"><canvas id="chartBrowser"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Operating Systems</h4>
      <div class="chart-wrap"><canvas id="chartOS"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Traffic Sources</h4>
      <div class="chart-wrap"><canvas id="chartTraffic"></canvas></div>
    </div>
    <div class="chart-card">
      <h4>Top Countries</h4>
      <div class="chart-wrap"><canvas id="chartCountries"></canvas></div>
    </div>
  </div>

  <!-- ══ TOP PAGES ══ -->
  <div class="section-title">Top Pages <span class="badge">Most visited</span></div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Page URL</th><th>Total Views</th><th>Unique Views</th><th>Avg Time</th></tr></thead>
        <tbody>
          <?php if (empty($topPages)): ?>
            <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--gray-400);">No page views recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($topPages as $tp): ?>
            <tr>
              <td style="max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($tp['page_url']) ?>"><?= htmlspecialchars($tp['page_url']) ?></td>
              <td><strong><?= number_format($tp['views']) ?></strong></td>
              <td><?= number_format($tp['unique_views']) ?></td>
              <td><?= formatDuration((int)$tp['avg_time']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══ VISITORS TABLE ══ -->
  <div class="section-title" style="margin-top:24px;">All Visitors <span class="badge"><?= number_format($totalFiltered) ?> total</span></div>
  <div class="card">
    <div class="section-header">
      <div class="filters">
        <form method="GET" action="" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
          <input type="text" name="search" placeholder="Search visitors..." value="<?= htmlspecialchars($search) ?>" style="min-width:180px;">
          <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
          <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
          <button type="submit" class="btn btn-gold">Search</button>
          <?php if ($search || $dateFrom || $dateTo): ?>
            <a href="analytics.php" class="btn btn-outline">Clear</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th><a href="?sort=session_id&order=<?= $sortBy==='session_id' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Session ID</a></th>
            <th><a href="?sort=ip_address&order=<?= $sortBy==='ip_address' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">IP</a></th>
            <th><a href="?sort=country&order=<?= $sortBy==='country' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Location</a></th>
            <th><a href="?sort=browser&order=<?= $sortBy==='browser' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Browser</a></th>
            <th><a href="?sort=os&order=<?= $sortBy==='os' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">OS</a></th>
            <th><a href="?sort=device_type&order=<?= $sortBy==='device_type' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Device</a></th>
            <th><a href="?sort=referrer_source&order=<?= $sortBy==='referrer_source' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Source</a></th>
            <th><a href="?sort=visitor_type&order=<?= $sortBy==='visitor_type' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Type</a></th>
            <th><a href="?sort=total_pages_viewed&order=<?= $sortBy==='total_pages_viewed' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Pages</a></th>
            <th><a href="?sort=session_duration&order=<?= $sortBy==='session_duration' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Duration</a></th>
            <th><a href="?sort=session_start&order=<?= $sortBy==='session_start' && $sortOrder==='ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Started</a></th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($visitors)): ?>
            <tr><td colspan="12" style="text-align:center;padding:40px;color:var(--gray-400);font-size:14px;">No visitors found. Track some page views first!</td></tr>
          <?php else: ?>
            <?php foreach ($visitors as $v): ?>
            <tr>
              <td style="font-family:monospace;font-size:12px;" title="<?= htmlspecialchars($v['session_id']) ?>"><?= htmlspecialchars(substr($v['session_id'], 0, 16)) ?>…</td>
              <td><?= htmlspecialchars($v['ip_address'] ?: '-') ?></td>
              <td><?= htmlspecialchars($v['city'] ?: '') ?><?= ($v['city'] && $v['country']) ? ', ' : '' ?><?= htmlspecialchars($v['country'] ?: '-') ?></td>
              <td><?= htmlspecialchars($v['browser'] ?: '-') ?> <?= $v['browser_version'] ? 'v' . htmlspecialchars($v['browser_version']) : '' ?></td>
              <td><?= htmlspecialchars($v['os'] ?: '-') ?></td>
              <td><span class="badge" style="background:var(--gray-100);color:var(--gray-600);"><?= htmlspecialchars($v['device_type']) ?></span></td>
              <td><?= htmlspecialchars($v['referrer_source'] ?: 'Direct') ?></td>
              <td><span class="badge <?= $v['visitor_type'] === 'new' ? 'badge-new' : 'badge-return' ?>"><?= $v['visitor_type'] ?></span></td>
              <td><?= (int)$v['total_pages_viewed'] ?></td>
              <td><?= formatDuration((int)$v['session_duration']) ?></td>
              <td style="white-space:nowrap;"><?= date('M j, H:i', strtotime($v['session_start'])) ?></td>
              <td><span class="<?= $v['is_online'] ? 'badge-online' : 'badge-offline' ?>"><?= $v['is_online'] ? 'Online' : 'Offline' ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?p=<?= $page-1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= urlencode($sortOrder) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">← Prev</a>
      <?php endif; ?>
      <?php for ($i = max(1, $page-3); $i <= min($totalPages, $page+3); $i++): ?>
        <a href="?p=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= urlencode($sortOrder) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?p=<?= $page+1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sortBy) ?>&order=<?= urlencode($sortOrder) ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>">Next →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ══ BREAKDOWN REPORTS ══ -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
    <!-- Device Report -->
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Device Report</div>
      <table>
        <thead><tr><th>Device</th><th>Visitors</th><th>%</th></tr></thead>
        <tbody>
          <?php $totalDevices = array_sum($deviceDist); ?>
          <?php 
$deviceIcons = ['desktop' => svg_icon('desktop',16).' Desktop', 'mobile' => svg_icon('mobile',16).' Mobile', 'tablet' => svg_icon('tablet',16).' Tablet'];
foreach ($deviceIcons as $key => $label): ?>
          <tr>
            <td><?= $label ?></td>
            <td><strong><?= number_format($deviceDist[$key] ?? 0) ?></strong></td>
            <td><?= $totalDevices > 0 ? round(($deviceDist[$key] ?? 0) / $totalDevices * 100, 1) : 0 ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Browser Report -->
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Browser Report</div>
      <table>
        <thead><tr><th>Browser</th><th>Visitors</th><th>%</th></tr></thead>
        <tbody>
          <?php $totalBrowsers = array_sum($browserDist);
          $browserColors = ['Chrome' => '#4285F4', 'Firefox' => '#FF7139', 'Safari' => '#006CFF', 'Edge' => '#0078D7', 'Opera' => '#FF1B2D'];
          ?>
          <?php if (empty($browserDist)): ?>
            <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray-400);">No data yet</td></tr>
          <?php else: ?>
            <?php foreach ($browserDist as $browser => $count): ?>
            <tr>
              <td><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:<?= $browserColors[$browser] ?? '#999' ?>;margin-right:6px;"></span><?= htmlspecialchars($browser ?: 'Unknown') ?></td>
              <td><strong><?= number_format($count) ?></strong></td>
              <td><?= $totalBrowsers > 0 ? round($count / $totalBrowsers * 100, 1) : 0 ?>%</td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- OS Report -->
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Operating System Report</div>
      <table>
        <thead><tr><th>OS</th><th>Visitors</th><th>%</th></tr></thead>
        <tbody>
          <?php $totalOS = array_sum($osDist); ?>
          <?php if (empty($osDist)): ?>
            <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray-400);">No data yet</td></tr>
          <?php else: ?>
            <?php foreach ($osDist as $os => $count): ?>
            <tr>
              <td><?= htmlspecialchars($os ?: 'Unknown') ?></td>
              <td><strong><?= number_format($count) ?></strong></td>
              <td><?= $totalOS > 0 ? round($count / $totalOS * 100, 1) : 0 ?>%</td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Country Report -->
    <div class="card">
      <div class="card-title" style="margin-bottom:12px;">Country Report</div>
      <table>
        <thead><tr><th>Country</th><th>Visitors</th><th>%</th></tr></thead>
        <tbody>
          <?php $totalCountries = array_sum($countryDist); ?>
          <?php if (empty($countryDist)): ?>
            <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--gray-400);">No data yet</td></tr>
          <?php else: ?>
            <?php foreach (array_slice($countryDist, 0, 15) as $country => $count): ?>
            <tr>
              <td><?= htmlspecialchars($country ?: 'Unknown') ?></td>
              <td><strong><?= number_format($count) ?></strong></td>
              <td><?= $totalCountries > 0 ? round($count / $totalCountries * 100, 1) : 0 ?>%</td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<footer>
  <p>© 2025 <span>Specora</span> Admin Panel. All rights reserved.</p>
</footer>
</div><!-- /sidebar-page -->

<script>
// ═══════════════════════════════════════════════
// CHARTS — Rendered with Chart.js
// ═══════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function() {

  // ── Daily Visitors Chart ──
  var dailyCtx = document.getElementById('chartDaily');
  if (dailyCtx) {
    var dailyData = <?= json_encode($visitorsByDay) ?>;
    new Chart(dailyCtx, {
      type: 'bar',
      data: {
        labels: dailyData.map(function(d) { return d.date ? d.date.substring(5) : ''; }),
        datasets: [{
          label: 'Visitors',
          data: dailyData.map(function(d) { return parseInt(d.visitors); }),
          backgroundColor: 'rgba(234, 189, 120, 0.6)',
          borderColor: 'rgba(234, 189, 120, 1)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

  // ── Weekly Visitors Chart ──
  var weeklyCtx = document.getElementById('chartWeekly');
  if (weeklyCtx) {
    var weeklyData = <?= json_encode($visitorsByWeek) ?>;
    new Chart(weeklyCtx, {
      type: 'line',
      data: {
        labels: weeklyData.map(function(d) { return d.week_start ? d.week_start.substring(5) : ''; }),
        datasets: [{
          label: 'Visitors',
          data: weeklyData.map(function(d) { return parseInt(d.visitors); }),
          borderColor: '#8B5CF6',
          backgroundColor: 'rgba(139, 92, 246, 0.1)',
          fill: true,
          tension: 0.3,
          pointRadius: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

  // ── Monthly Visitors Chart ──
  var monthlyCtx = document.getElementById('chartMonthly');
  if (monthlyCtx) {
    var monthlyData = <?= json_encode($visitorsByMonth) ?>;
    new Chart(monthlyCtx, {
      type: 'bar',
      data: {
        labels: monthlyData.map(function(d) { return d.yearmonth || ''; }),
        datasets: [{
          label: 'Visitors',
          data: monthlyData.map(function(d) { return parseInt(d.visitors); }),
          backgroundColor: 'rgba(16, 185, 129, 0.6)',
          borderColor: 'rgba(16, 185, 129, 1)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

  // ── Device Distribution Chart (Doughnut) ──
  var deviceCtx = document.getElementById('chartDevice');
  if (deviceCtx) {
    var deviceData = <?= json_encode($deviceDist) ?>;
    var deviceLabels = Object.keys(deviceData);
    var deviceValues = Object.values(deviceData);
    new Chart(deviceCtx, {
      type: 'doughnut',
      data: {
        labels: deviceLabels,
        datasets: [{
          data: deviceValues,
          backgroundColor: ['rgba(234, 189, 120, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(139, 92, 246, 0.8)'],
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } }
      }
    });
  }

  // ── Browser Distribution Chart ──
  var browserCtx = document.getElementById('chartBrowser');
  if (browserCtx) {
    var browserData = <?= json_encode($browserDist) ?>;
    var browserLabels = Object.keys(browserData);
    var browserValues = Object.values(browserData);
    var browserColors = [];
    var colorMap = { 'Chrome': '#4285F4', 'Firefox': '#FF7139', 'Safari': '#006CFF', 'Edge': '#0078D7', 'Opera': '#FF1B2D' };
    browserLabels.forEach(function(l) { browserColors.push(colorMap[l] || '#999'); });

    new Chart(browserCtx, {
      type: 'bar',
      data: {
        labels: browserLabels,
        datasets: [{
          label: 'Visitors',
          data: browserValues,
          backgroundColor: browserColors,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

  // ── OS Distribution Chart ──
  var osCtx = document.getElementById('chartOS');
  if (osCtx) {
    var osData = <?= json_encode($osDist) ?>;
    var osLabels = Object.keys(osData);
    var osValues = Object.values(osData);
    new Chart(osCtx, {
      type: 'doughnut',
      data: {
        labels: osLabels,
        datasets: [{
          data: osValues,
          backgroundColor: ['#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EF4444', '#6B7280'],
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }
      }
    });
  }

  // ── Traffic Sources Chart (Doughnut) ──
  var trafficCtx = document.getElementById('chartTraffic');
  if (trafficCtx) {
    var trafficData = <?= json_encode($trafficSources) ?>;
    var trafficLabels = Object.keys(trafficData);
    var trafficValues = Object.values(trafficData);
    var trafficColors = ['#EABD78', '#4285F4', '#0078D7', '#FF7139', '#1877F2', '#E4405F', '#1DA1F2', '#0A66C2', '#10B981', '#8B5CF6'];
    new Chart(trafficCtx, {
      type: 'pie',
      data: {
        labels: trafficLabels,
        datasets: [{
          data: trafficValues,
          backgroundColor: trafficColors.slice(0, trafficLabels.length),
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } } }
      }
    });
  }

  // ── Countries Chart ──
  var countryCtx = document.getElementById('chartCountries');
  if (countryCtx) {
    var countryData = <?= json_encode(array_slice($countryDist, 0, 10)) ?>;
    var countryLabels = Object.keys(countryData);
    var countryValues = Object.values(countryData);
    new Chart(countryCtx, {
      type: 'bar',
      data: {
        labels: countryLabels,
        datasets: [{
          label: 'Visitors',
          data: countryValues,
          backgroundColor: 'rgba(139, 92, 246, 0.6)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

});
</script>

</body>
</html>
