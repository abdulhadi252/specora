<?php
/**
 * admin/contact-messages.php — Specora Contact Messages Manager
 * 
 * Protected admin page to view and delete contact form submissions.
 * No approval workflow — only delete action.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/connect.php';

require_admin_login();

require_once __DIR__ . '/../includes/check_tables.php';
check_specora_tables();

// ── AJAX Delete Handler ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax_action']) && $_POST['ajax_action'] === 'delete') {
    header('Content-Type: application/json');
    
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'message' => 'Invalid form token.']);
        exit;
    }
    
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $connect->prepare("DELETE FROM contact_messages WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Message deleted.', 'deleted_id' => $id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Message not found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}

// ── Handle form-based delete (backward compat) ──
$actionMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $actionMsg = 'Invalid form token. Please refresh the page.';
    } else {
        $msgId = (int)($_POST['id'] ?? 0);
        if ($msgId) {
            $stmt = $connect->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->bind_param('i', $msgId);
            $stmt->execute();
            $stmt->close();
            $actionMsg = 'Message deleted.';
        }
    }
}

// ── Stats ──
$totalCount = 0;
$totalRes = mysqli_query($connect, "SELECT COUNT(*) as c FROM contact_messages");
if ($totalRes) {
    $totalCount = (int)mysqli_fetch_assoc($totalRes)['c'];
}

// ── Search ──
$searchQuery = trim($_GET['search'] ?? '');

// ── Fetch messages ──
$where = '';
$params = [];
$types = '';

if (!empty($searchQuery)) {
    $where = "WHERE (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $searchLike = '%' . $searchQuery . '%';
    $params = [$searchLike, $searchLike, $searchLike, $searchLike];
    $types = 'ssss';
}

$messages = [];
$sql = "SELECT id, name, email, subject, message, created_at FROM contact_messages {$where} ORDER BY created_at DESC LIMIT 100";
$stmt = $connect->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Contact Messages — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}
.main{padding:40px 48px;flex:1;max-width:1200px;margin:0 auto;width:100%;}
.page-header{margin-bottom:28px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:6px;}
.page-sub{font-size:15px;color:var(--gray-600);}
.alert{padding:12px 18px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:14px;font-weight:500;}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#065F46;}
.search-bar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;}
.search-input{flex:1;min-width:200px;font-family:var(--font-body);font-size:14px;padding:10px 16px;border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);outline:none;transition:all .2s;background:var(--white);}
.search-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
.search-btn{font-size:13px;font-weight:600;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:10px 24px;cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.search-btn:hover{background:var(--gold-dark);color:var(--white);}
.search-btn-outline{background:var(--gray-100);color:var(--gray-600);}
.search-btn-outline:hover{background:var(--gray-200);color:var(--black);}
.table-wrap{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.04);}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray-400);padding:14px 16px;border-bottom:1px solid var(--gray-200);background:var(--off-white);}
td{padding:14px 16px;border-bottom:1px solid var(--gray-100);font-size:14px;color:var(--gray-600);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--off-white);}
.msg-preview{display:block;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:var(--gray-400);}
.date-cell{font-size:12px;white-space:nowrap;color:var(--gray-400);}
.actions-cell{white-space:nowrap;}
.action-btn{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;transition:all .2s;background:var(--gray-100);color:var(--gray-600);text-decoration:none;font-family:var(--font-body);}
.action-btn:hover{transform:translateY(-1px);}
.action-delete{background:rgba(239,68,68,.08);color:#EF4444;}
.action-delete:hover{background:rgba(239,68,68,.15);}
.empty-state{text-align:center;padding:60px 20px;color:var(--gray-400);}
.empty-icon{font-size:48px;margin-bottom:12px;color:var(--gray-200);}
.empty-text{font-size:16px;font-weight:600;}
.empty-sub{font-size:14px;margin-top:4px;}
.ajax-toast{position:fixed;bottom:24px;right:24px;z-index:9999;padding:14px 20px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,.12);opacity:0;transform:translateY(12px);transition:all .3s ease;pointer-events:none;max-width:360px;}
.ajax-toast.show{opacity:1;transform:translateY(0);pointer-events:all;}
.ajax-toast.success{background:#065F46;color:#fff;}
.ajax-toast.error{background:#EF4444;color:#fff;}
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .table-wrap{overflow-x:auto;}
  table{min-width:700px;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
  .actions-cell{flex-direction:column;align-items:stretch;}
  .action-btn{justify-content:center;}
}
</style>
</head>
<body>

<?php $currentPage = 'contact-messages'; include __DIR__ . '/sidebar.php'; ?>

<div class="sidebar-page">
<div class="main">

  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title"><i class="fas fa-envelope" style="color:var(--gold);margin-right:8px;"></i>Contact Messages</h1>
    <p class="page-sub">View and manage contact form submissions. Total: <strong><?= $totalCount ?></strong> messages.</p>
  </div>

  <?php if ($actionMsg): ?>
    <div class="alert alert-success" id="pageAlert"><?= htmlspecialchars($actionMsg) ?></div>
  <?php endif; ?>

  <div id="ajaxToast" class="ajax-toast"></div>

  <form method="GET" class="search-bar">
    <input type="text" name="search" class="search-input" placeholder="Search by name, email, subject, or message..." value="<?= htmlspecialchars($searchQuery) ?>">
    <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
    <?php if (!empty($searchQuery)): ?><a href="contact-messages.php" class="search-btn search-btn-outline"><i class="fas fa-times"></i> Clear</a><?php endif; ?>
  </form>

  <div class="table-wrap">
    <table id="messagesTable">
      <thead><tr>
        <th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php if (empty($messages)): ?>
          <tr><td colspan="6">
            <div class="empty-state">
              <div class="empty-icon"><i class="fas fa-inbox"></i></div>
              <div class="empty-text">No messages found</div>
              <div class="empty-sub"><?= !empty($searchQuery) ? 'Try a different search term.' : 'No contact form submissions yet.' ?></div>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <tr id="row-<?= $msg['id'] ?>">
              <td style="font-weight:600;color:var(--black);"><?= htmlspecialchars($msg['name']) ?></td>
              <td><a href="mailto:<?= htmlspecialchars($msg['email']) ?>" style="color:var(--gold-dark);"><?= htmlspecialchars($msg['email']) ?></a></td>
              <td><?= htmlspecialchars(mb_substr($msg['subject'] ?: '—', 0, 30)) ?></td>
              <td><span class="msg-preview"><?= htmlspecialchars(mb_substr($msg['message'], 0, 60)) ?></span></td>
              <td class="date-cell"><?= date('M j, g:i A', strtotime($msg['created_at'])) ?></td>
              <td class="actions-cell">
                <button class="action-btn action-delete" onclick="ajaxDelete(<?= $msg['id'] ?>)"><i class="fas fa-trash"></i> Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<footer style="background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);">
  <p>&copy; 2025 <span style="color:var(--gold);">Specora</span> Admin Panel. All rights reserved.</p>
</footer>

</div><!-- /sidebar-page -->

<script>
var csrfToken = '<?= addslashes(generate_csrf_token()) ?>';

function showToast(message, type) {
    var t = document.getElementById('ajaxToast');
    t.textContent = message;
    t.className = 'ajax-toast ' + type + ' show';
    setTimeout(function() { t.classList.remove('show'); }, 3000);
}

function ajaxDelete(id) {
    if (!confirm('Delete this message permanently? This cannot be undone.')) return;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showToast(res.message, 'success');
                        var row = document.getElementById('row-' + id);
                        if (row) {
                            row.style.transition = 'all .3s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(20px)';
                            setTimeout(function() {
                                if (row.parentNode) row.parentNode.removeChild(row);
                                setTimeout(function() { window.location.reload(); }, 500);
                            }, 300);
                        }
                    } else {
                        showToast(res.message, 'error');
                    }
                } catch(e) {
                    showToast('Server error. Please refresh.', 'error');
                }
            } else {
                showToast('Server error. Please refresh.', 'error');
            }
        }
    };
    xhr.send('ajax_action=delete&id=' + id + '&csrf_token=' + encodeURIComponent(csrfToken));
}
</script>

</body>
</html>
