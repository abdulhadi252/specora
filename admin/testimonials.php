<?php
/**
 * admin/testimonials.php — Specora Testimonials Manager
 * 
 * Protected admin page to manage testimonials with approve/reject/delete workflow.
 * Status is managed via an inline dropdown in the Status column.
 * Uses AJAX for smooth actions without page refresh.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';
require_once __DIR__ . '/../includes/connect.php';

require_admin_login();

require_once __DIR__ . '/../includes/check_tables.php';
check_specora_tables();

// ── AJAX Handler ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'message' => 'Invalid form token.']);
        exit;
    }
    
    $action = $_POST['ajax_action'];
    $id = (int)($_POST['id'] ?? 0);
    $response = ['success' => false, 'message' => 'Unknown error.'];
    
    switch ($action) {
        case 'approve':
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $response = $stmt->affected_rows > 0
                    ? ['success' => true, 'message' => 'Testimonial approved! It will now appear on the website.']
                    : ['success' => false, 'message' => 'Testimonial not found.'];
                $stmt->close();
            }
            break;
            
        case 'reject':
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $response = $stmt->affected_rows > 0
                    ? ['success' => true, 'message' => 'Testimonial rejected.']
                    : ['success' => false, 'message' => 'Testimonial not found.'];
                $stmt->close();
            }
            break;
            
        case 'pending':
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'pending' WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $response = $stmt->affected_rows > 0
                    ? ['success' => true, 'message' => 'Testimonial moved back to pending.']
                    : ['success' => false, 'message' => 'Testimonial not found.'];
                $stmt->close();
            }
            break;
            
        case 'delete':
            $stmt = $connect->prepare("DELETE FROM testimonials WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $response = ['success' => true, 'message' => 'Testimonial deleted.', 'deleted_id' => $id];
                } else {
                    $response = ['success' => false, 'message' => 'Testimonial not found.'];
                }
                $stmt->close();
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

// ── Handle form-based actions (backward compat) ──
$actionMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && empty($_POST['ajax_action'])) {
    
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $actionMsg = 'Invalid form token. Please refresh the page.';
    } else {
        $msgId = (int)($_POST['id'] ?? 0);
        
        if ($_POST['action'] === 'approve' && $msgId) {
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
            $stmt->bind_param('i', $msgId); $stmt->execute(); $stmt->close();
            $actionMsg = 'Testimonial approved!';
        } elseif ($_POST['action'] === 'reject' && $msgId) {
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param('i', $msgId); $stmt->execute(); $stmt->close();
            $actionMsg = 'Testimonial rejected.';
        } elseif ($_POST['action'] === 'pending' && $msgId) {
            $stmt = $connect->prepare("UPDATE testimonials SET status = 'pending' WHERE id = ?");
            $stmt->bind_param('i', $msgId); $stmt->execute(); $stmt->close();
            $actionMsg = 'Testimonial moved back to pending.';
        } elseif ($_POST['action'] === 'delete' && $msgId) {
            $stmt = $connect->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->bind_param('i', $msgId); $stmt->execute(); $stmt->close();
            $actionMsg = 'Testimonial deleted.';
        }
    }
}

// ── Filters ──
$statusFilter = $_GET['status'] ?? 'all';

// ── Fetch testimonials ──
$where = '';
$params = [];
$types = '';

if ($statusFilter !== 'all') {
    $where = "WHERE status = ?";
    $params[] = $statusFilter;
    $types = 's';
}

$testimonials = [];
$sql = "SELECT * FROM testimonials {$where} ORDER BY created_at DESC LIMIT 100";
$stmt = $connect->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$testimonials = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Testimonials — Specora Admin</title>
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

/* ── TABLE ── */
.table-wrap{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.04);}
table{width:100%;border-collapse:collapse;}
th{text-align:left;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--gray-400);padding:14px 16px;border-bottom:1px solid var(--gray-200);background:var(--off-white);}
td{padding:14px 16px;border-bottom:1px solid var(--gray-100);font-size:14px;color:var(--gray-600);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--off-white);}
.feedback-preview{display:block;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;color:var(--gray-400);}
.date-cell{font-size:12px;white-space:nowrap;color:var(--gray-400);}
.actions-cell{white-space:nowrap;}
.action-btn{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;transition:all .2s;background:var(--gray-100);color:var(--gray-600);text-decoration:none;font-family:var(--font-body);}
.action-btn:hover{transform:translateY(-1px);}
.action-delete{background:rgba(239,68,68,.08);color:#EF4444;}
.action-delete:hover{background:rgba(239,68,68,.15);}

/* ── STATUS DROPDOWN (inline in Status column) ── */
.status-select{font-family:var(--font-body);font-size:11px;font-weight:700;padding:5px 26px 5px 10px;border-radius:var(--radius-pill);text-transform:uppercase;letter-spacing:.5px;cursor:pointer;outline:none;border:1.5px solid transparent;transition:all .2s;min-width:95px;appearance:none;-webkit-appearance:none;background-repeat:no-repeat;background-position:right 8px center;background-size:8px;}
.status-select.status-pending{background-color:rgba(234,189,120,.12);color:var(--gold-dark);border-color:rgba(234,189,120,.25);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23C9973A' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");}
.status-select.status-approved{background-color:rgba(16,185,129,.08);color:#065F46;border-color:rgba(16,185,129,.2);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23065F46' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");}
.status-select.status-rejected{background-color:rgba(239,68,68,.08);color:#EF4444;border-color:rgba(239,68,68,.2);background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' fill='none' stroke='%23EF4444' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");}
.status-select:focus{border-color:var(--gold);box-shadow:0 0 0 2px rgba(234,189,120,.15);}

.empty-state{text-align:center;padding:60px 20px;color:var(--gray-400);}
.empty-icon{font-size:48px;margin-bottom:12px;color:var(--gray-200);}
.empty-text{font-size:16px;font-weight:600;}
.empty-sub{font-size:14px;margin-top:4px;}

/* ── AJAX TOAST ── */
.ajax-toast{position:fixed;bottom:24px;right:24px;z-index:9999;padding:14px 20px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,.12);opacity:0;transform:translateY(12px);transition:all .3s ease;pointer-events:none;max-width:360px;}
.ajax-toast.show{opacity:1;transform:translateY(0);pointer-events:all;}
.ajax-toast.success{background:#065F46;color:#fff;}
.ajax-toast.error{background:#EF4444;color:#fff;}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .main{padding:32px 24px;}
  .table-wrap{overflow-x:auto;}
  table{min-width:650px;}
}
@media(max-width:768px){
  .main{padding:24px 20px;}
}
</style>
</head>
<body>

<?php $currentPage = 'testimonials'; include __DIR__ . '/sidebar.php'; ?>

<div class="sidebar-page">
<div class="main">

  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title"><i class="fas fa-star" style="color:var(--gold);margin-right:8px;"></i>Testimonials</h1>
    <p class="page-sub">Manage customer testimonials. Change status inline or delete permanently.</p>
  </div>

  <?php if ($actionMsg): ?>
    <div class="alert alert-success" id="pageAlert"><?= htmlspecialchars($actionMsg) ?></div>
  <?php endif; ?>
  <div id="ajaxToast" class="ajax-toast"></div>

  <!-- ══ TABLE ══ -->
  <div class="table-wrap">
    <table id="testimonialsTable">
      <thead><tr>
        <th>Name</th><th>Email</th><th>City</th><th>Feedback</th><th>Status</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php if (empty($testimonials)): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-icon"><i class="fas fa-star"></i></div>
              <div class="empty-text">No testimonials found</div>
              <div class="empty-sub"><?= $statusFilter !== 'all' ? 'No testimonials with this status.' : 'No testimonials yet.' ?></div>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($testimonials as $tm): ?>
            <tr id="row-<?= $tm['id'] ?>">
              <td style="font-weight:600;color:var(--black);"><?= htmlspecialchars($tm['name']) ?></td>
              <td><?= $tm['email'] ? '<a href="mailto:'.htmlspecialchars($tm['email']).'" style="color:var(--gold-dark);">'.htmlspecialchars($tm['email']).'</a>' : '<span style="color:var(--gray-400);">—</span>' ?></td>
              <td><?= htmlspecialchars($tm['city'] ?: '—') ?></td>
              <td><span class="feedback-preview"><?= htmlspecialchars(mb_substr($tm['feedback'], 0, 60)) ?></span></td>
              <td>
                <select class="status-select status-<?= $tm['status'] ?>" id="status-<?= $tm['id'] ?>" onchange="handleSelect(this, <?= $tm['id'] ?>)">
                  <option value="approve" <?= $tm['status'] === 'approved' ? 'selected' : '' ?>>&check; Approved</option>
                  <option value="pending" <?= $tm['status'] === 'pending' ? 'selected' : '' ?>>&#8987; Pending</option>
                  <option value="reject" <?= $tm['status'] === 'rejected' ? 'selected' : '' ?>>&#10007; Rejected</option>
                </select>
              </td>
              <td class="date-cell"><?= date('M j, g:i A', strtotime($tm['created_at'])) ?></td>
              <td class="actions-cell">
                <button class="action-btn action-delete" onclick="ajaxDelete(<?= $tm['id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
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

// ── AJAX Action: approve / reject / pending ──
function ajaxAction(action, id) {
    var confirmMsg = '';
    if (action === 'reject') {
        confirmMsg = 'Are you sure you want to reject this testimonial?';
    } else if (action === 'pending') {
        confirmMsg = 'Move this testimonial back to pending?';
    }
    if (confirmMsg && !confirm(confirmMsg)) return;

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
                        updateRowStatus(id, action);
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
    xhr.send('ajax_action=' + action + '&id=' + id + '&csrf_token=' + encodeURIComponent(csrfToken));
}

// ── AJAX Delete with confirmation ──
function ajaxDelete(id) {
    if (!confirm('Remove this testimonial permanently? This cannot be undone.')) return;

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

// ── Handle status dropdown change ──
function handleSelect(sel, id) {
    var action = sel.value;
    if (!action) return;
    if (action === 'reject' && !confirm('Are you sure you want to reject this testimonial?')) {
        // Revert by reloading
        window.location.reload();
        return;
    }
    ajaxAction(action, id);
}

// ── Update dropdown after status change ──
function updateRowStatus(id, newAction) {
    var sel = document.getElementById('status-' + id);
    if (!sel) {
        window.location.reload();
        return;
    }

    var newStatus = '';
    if (newAction === 'approve') newStatus = 'approved';
    else if (newAction === 'reject') newStatus = 'rejected';
    else if (newAction === 'pending') newStatus = 'pending';

    if (!newStatus) {
        window.location.reload();
        return;
    }

    // Update class
    sel.className = 'status-select status-' + newStatus;
    // Update selected value
    sel.value = newAction;
}
</script>

</body>
</html>
