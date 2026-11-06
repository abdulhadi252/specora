<?php
/**
 * admin/manage_category.php — Specora Category Manager
 * 
 * Manages categories from the existing `admin_category` table in specora DB.
 * Each row = 1 main category + 4 sub-categories (sub1_name/sub1_slug through sub4_name/sub4_slug).
 * Changes reflect immediately on index.php and category.php.
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin_functions.php';

require_admin_login();

// ── Handle Actions ──
$actionMsg = '';
$actionError = '';

// ── DELETE Category ──
if (isset($_GET['delete']) && validate_csrf_token($_GET['token'] ?? '')) {
    $delSlug = trim($_GET['delete']);
    if ($delSlug) {
        $delStmt = $connect->prepare("DELETE FROM admin_category WHERE category_slug = ?");
        if ($delStmt) {
            $delStmt->bind_param("s", $delSlug);
            $delStmt->execute();
            $delStmt->close();
        }
        http_response_code(302);
        header('Location: manage_category.php');
        exit;
    }
}

// ── Helper: generate slug ──
function makeSlug($name) {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// ── SAVE / UPDATE Category ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $actionError = 'Invalid form token. Please refresh the page.';
    } else {
        $editMode   = isset($_POST['edit_mode']) && $_POST['edit_mode'] === '1';
        $oldKey     = trim($_POST['old_key'] ?? '');
        $mainName   = trim($_POST['label'] ?? '');      // display name
        $mainSlug   = trim($_POST['group_key'] ?? '');   // slug
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        // Validate
        if (empty($mainSlug) || empty($mainName)) {
            $actionError = 'Category name and slug are required.';
        } else {
            // Check duplicate slug (in add mode and edit mode — exclude current record on edit)
            $checkStmt = $connect->prepare("SELECT id FROM admin_category WHERE category_slug = ?" . ($editMode ? " AND category_slug != ?" : ""));
            $slugExists = false;
            if ($checkStmt) {
                if ($editMode) {
                    $checkStmt->bind_param("ss", $mainSlug, $oldKey);
                } else {
                    $checkStmt->bind_param("s", $mainSlug);
                }
                $checkStmt->execute();
                $checkStmt->store_result();
                $slugExists = $checkStmt->num_rows > 0;
                $checkStmt->close();
            }
            if ($slugExists) {
                $actionError = 'A category with slug "' . htmlspecialchars($mainSlug) . '" already exists.';
            }

            if (empty($actionError)) {
                // Build sub-category fields (up to 4)
                $subLabels = $_POST['sub_label'] ?? [];
                $subKeys   = $_POST['sub_key'] ?? [];
                $subNames  = ['', '', '', ''];
                $subSlugs  = ['', '', '', ''];
                for ($i = 0; $i < 4; $i++) {
                    $label = trim($subLabels[$i] ?? '');
                    $key   = trim($subKeys[$i] ?? '');
                    if (!empty($label) && !empty($key)) {
                        $subNames[$i] = $label;
                        $subSlugs[$i] = $key;
                    }
                }

                if ($editMode && !empty($oldKey)) {
                    $stmt = $connect->prepare("UPDATE admin_category SET 
                                category_name = ?,
                                category_slug = ?,
                                sort_order = ?,
                                sub1_name = ?, sub1_slug = ?,
                                sub2_name = ?, sub2_slug = ?,
                                sub3_name = ?, sub3_slug = ?,
                                sub4_name = ?, sub4_slug = ?
                            WHERE category_slug = ?");
                } else {
                    $stmt = $connect->prepare("INSERT INTO admin_category 
                                (category_name, category_slug, sort_order,
                                 sub1_name, sub1_slug, sub2_name, sub2_slug,
                                 sub3_name, sub3_slug, sub4_name, sub4_slug)
                            VALUES 
                                (?, ?, ?,
                                 ?, ?,
                                 ?, ?,
                                 ?, ?,
                                 ?, ?)");
                }

                if ($stmt) {
                    if ($editMode && !empty($oldKey)) {
                        $stmt->bind_param("ssisssssssss",
                            $mainName, $mainSlug, $sortOrder,
                            $subNames[0], $subSlugs[0],
                            $subNames[1], $subSlugs[1],
                            $subNames[2], $subSlugs[2],
                            $subNames[3], $subSlugs[3],
                            $oldKey);
                    } else {
                        $stmt->bind_param("ssissssssss",
                            $mainName, $mainSlug, $sortOrder,
                            $subNames[0], $subSlugs[0],
                            $subNames[1], $subSlugs[1],
                            $subNames[2], $subSlugs[2],
                            $subNames[3], $subSlugs[3]);
                    }

                    if ($stmt->execute()) {
                        $actionMsg = $editMode ? 'Category updated successfully!' : 'Category created successfully!';
                    } else {
                        $actionError = 'Database error: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $actionError = 'Database error: ' . $connect->error;
                }
            }
        }
    }
}

// ── Fetch all categories from admin_category table ──
$allGroups = [];
$gRes = $connect->query("SELECT * FROM admin_category ORDER BY sort_order ASC, created_at ASC");
if ($gRes) {
    while ($g = $gRes->fetch_assoc()) {
        $gKey = $g['category_slug'];
        // Build subs array from sub1-sub4 columns
        $subs = [];
        for ($si = 1; $si <= 4; $si++) {
            $sName = $g["sub{$si}_name"] ?? '';
            $sSlug = $g["sub{$si}_slug"] ?? '';
            if (!empty($sName)) {
                $subs[] = [
                    'label'   => $sName,
                    'sub_key' => $sSlug,
                    'icon'    => '🔖',
                ];
            }
        }
        $allGroups[$gKey] = [
            'group_key'     => $g['category_slug'],
            'label'         => $g['category_name'],
            'icon'          => '📁',
            'color'         => '#6366F1',
            'sort_order'    => $g['sort_order'],
            'subs'          => $subs,
        ];
    }
}

// ── Edit mode: fetch data for editing ──
$editData = null;
if (isset($_GET['edit'])) {
    $editKey = trim($_GET['edit']);
    if (isset($allGroups[$editKey])) {
        $editData = $allGroups[$editKey];
    }
}

// ── Fetch available subcategories from categories table (for dropdown) ──
$availableGroups = [];
$avRes = $connect->query("SELECT DISTINCT category_name FROM categories WHERE category_name != '' ORDER BY category_name ASC");
if ($avRes) {
    while ($av = $avRes->fetch_assoc()) {
        $cat   = $av['category_name'];
        $slug  = makeSlug($cat);
        $label = ucwords($cat);
        $availableGroups[$slug] = [
            'group_key' => $slug,
            'label'     => $label,
            'icon'      => '📁',
        ];
    }
}

// ── Stats ──
$totalGroups = count($allGroups);
$totalSubs = 0;
foreach ($allGroups as $g) { $totalSubs += count($g['subs']); }

$currentPage = 'manage-category';
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Manage Categories — Specora Admin</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-sm:10px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--gray-100);color:var(--black);min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;color:inherit;}
img{max-width:100%;display:block;}

.main{padding:40px 48px;flex:1;max-width:1200px;margin:0 auto;width:100%;}
.page-header{margin-bottom:28px;}
.page-eyebrow{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-dark);margin-bottom:6px;}
.page-title{font-family:var(--font-display);font-size:clamp(24px,3.5vw,36px);font-weight:800;color:var(--black);letter-spacing:-1px;margin-bottom:6px;}
.page-sub{font-size:15px;color:var(--gray-600);}

/* ALERT */
.alert{padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:14px;font-weight:500;display:flex;align-items:center;gap:10px;}
.alert-success{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#065F46;}
.alert-error{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}
.alert-close{margin-left:auto;background:none;border:none;font-size:18px;cursor:pointer;color:inherit;opacity:.5;}
.alert-close:hover{opacity:1;}

/* STATS BAR */
.stats-bar{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;}
.stat-chip{display:flex;align-items:center;gap:8px;background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-pill);padding:8px 16px;font-size:13px;font-weight:600;color:var(--gray-600);}
.stat-chip .chip-num{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--gold-dark);}

/* CARDS */
.card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);padding:28px;box-shadow:0 4px 16px rgba(0,0,0,.04);margin-bottom:24px;}
.card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--gray-200);}
.card-title{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--black);letter-spacing:-.3px;}
.card-sub{font-size:13px;color:var(--gray-400);margin-top:2px;}
.card-actions{display:flex;gap:8px;flex-shrink:0;}

/* FIELD */
.field-group{margin-bottom:18px;}
.field-label{display:block;font-size:11px;font-weight:700;color:var(--gray-600);letter-spacing:.5px;text-transform:uppercase;margin-bottom:6px;}
.field-hint{font-size:11px;color:var(--gray-400);margin-top:4px;line-height:1.5;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.field-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.field-row-4{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:14px;}
@media(max-width:768px){.field-row,.field-row-3,.field-row-4{grid-template-columns:1fr;}}

input[type="text"],input[type="color"],input[type="number"],textarea,select{width:100%;font-family:var(--font-body);font-size:14px;color:var(--black);background:var(--gray-100);border:1.5px solid transparent;border-radius:var(--radius-sm);padding:11px 14px;outline:none;transition:all .2s;}
input:focus,textarea:focus,select:focus{border-color:var(--gold);background:var(--white);box-shadow:0 0 0 3px rgba(234,189,120,.1);}
input[type="color"]{height:42px;padding:4px;cursor:pointer;}
.color-input-wrap{display:flex;align-items:center;gap:10px;}
.color-input-wrap input[type="color"]{width:50px;flex-shrink:0;}
.color-input-wrap input[type="text"]{flex:1;}
select{cursor:pointer;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23A8A49C' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px;}

/* SUB REPEATER */
.sub-repeater{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:6px;}
.sub-item{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:14px;position:relative;}
.sub-item .sub-num{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--gray-400);margin-bottom:8px;}
.sub-item .field-row{gap:8px;}
@media(max-width:768px){.sub-repeater{grid-template-columns:1fr;}}

/* BUTTONS */
.btn-primary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);background:var(--gold);border:none;border-radius:var(--radius-pill);padding:12px 28px;cursor:pointer;transition:all .2s;}
.btn-primary:hover{background:var(--gold-dark);color:var(--white);transform:translateY(-1px);}
.btn-secondary{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--gray-600);background:var(--white);border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);padding:11px 22px;cursor:pointer;transition:all .2s;}
.btn-secondary:hover{border-color:var(--gray-400);color:var(--black);}
.btn-danger{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:12px;font-weight:600;color:#EF4444;background:rgba(239,68,68,.06);border:1.5px solid rgba(239,68,68,.15);border-radius:var(--radius-pill);padding:8px 16px;cursor:pointer;transition:all .2s;}
.btn-danger:hover{background:rgba(239,68,68,.12);}
.btn-view{display:inline-flex;align-items:center;gap:8px;font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--gold-dark);background:rgba(234,189,120,.08);border:1px solid rgba(234,189,120,.2);border-radius:var(--radius-pill);padding:11px 22px;cursor:pointer;transition:all .2s;text-decoration:none;}
.btn-view:hover{background:rgba(234,189,120,.15);}
.btn-sm{font-size:11px;padding:6px 14px;}
.btn-icon{width:32px;height:32px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:14px;transition:all .2s;}

/* CATEGORY LIST */
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;}
.cat-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;transition:all .2s;}
.cat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.06);}
.cat-card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--gray-100);}
.cat-card-left{display:flex;align-items:center;gap:12px;}
.cat-card-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.cat-card-info{}
.cat-card-name{font-family:var(--font-display);font-size:15px;font-weight:700;color:var(--black);}
.cat-card-key{font-size:11px;color:var(--gray-400);}
.cat-card-actions{display:flex;gap:6px;}
.cat-card-body{padding:12px 18px 14px;}
.sub-list{display:flex;flex-direction:column;gap:6px;}
.sub-list-item{display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:6px;background:var(--gray-50);border:1px solid var(--gray-100);}
.sub-list-icon{font-size:14px;width:20px;text-align:center;flex-shrink:0;}
.sub-list-label{font-size:13px;color:var(--gray-700);flex:1;}
.sub-list-key{font-size:11px;color:var(--gray-400);}
.empty-subs{padding:12px 0;text-align:center;color:var(--gray-400);font-size:13px;font-style:italic;}

/* FORM INLINE HELP */
.form-help{font-size:11px;color:var(--gray-400);margin-top:3px;}
.slug-preview{font-size:11px;color:var(--gray-400);padding:3px 0;}
.slug-preview span{color:var(--gold-dark);font-weight:600;}

/* CANCEL EDIT */
.cancel-edit{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--gray-400);cursor:pointer;padding:6px 12px;border-radius:var(--radius-pill);transition:all .2s;text-decoration:none;}
.cancel-edit:hover{color:#EF4444;background:rgba(239,68,68,.06);}

/* RESPONSIVE */
@media(max-width:1024px){.main{padding:32px 24px;}.cat-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:768px){.main{padding:24px 20px;}.cat-grid{grid-template-columns:1fr;}.card{padding:20px;}.card-header{flex-direction:column;}}
</style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="sidebar-page">
<div class="main">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="page-eyebrow">Admin Panel</div>
    <h1 class="page-title"><?= svg_icon('folder', 24) ?> Manage Categories</h1>
    <p class="page-sub">Add, edit, or delete categories. Sub-categories auto-link to product categories from the database. Changes reflect on the homepage and category sidebar.</p>
  </div>

  <?php if ($actionMsg): ?>
    <div class="alert alert-success">
      <?= svg_icon('check', 16) ?> <?= htmlspecialchars($actionMsg) ?>
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>
  <?php if ($actionError): ?>
    <div class="alert alert-error">
      <?= svg_icon('cross', 16) ?> <?= htmlspecialchars($actionError) ?>
      <button class="alert-close" onclick="this.parentElement.remove()"><?= svg_icon('x', 12) ?></button>
    </div>
  <?php endif; ?>

  <!-- STATS BAR -->
  <div class="stats-bar">
    <div class="stat-chip">
      <?= svg_icon('folder', 16) ?> Categories <span class="chip-num"><?= $totalGroups ?></span>
    </div>
    <div class="stat-chip">
      <?= svg_icon('bookmark', 16) ?> Subcategories <span class="chip-num"><?= $totalSubs ?></span>
    </div>
    <a href="?add=1" class="stat-chip" style="cursor:pointer;text-decoration:none;background:var(--gold);color:var(--black);font-weight:700;" onclick="event.preventDefault();document.getElementById('addForm').scrollIntoView({behavior:'smooth'});document.getElementById('addForm').style.display='block'">
      ＋ Add New Category
    </a>
  </div>

  <!-- ── ADD / EDIT FORM ── -->
  <div class="card" id="addForm" style="<?= (!isset($_GET['add']) && !$editData) ? 'display:none' : '' ?>">
    <div class="card-header">
      <div>
        <div class="card-title"><?= $editData ? svg_icon('edit', 18).' Edit Category' : svg_icon('plus', 18).' Add New Category' ?></div>
        <div class="card-sub"><?= $editData ? 'Update the main category and its sub-categories.' : 'Create a main category with up to 4 sub-categories.' ?></div>
      </div>
      <div class="card-actions">
        <a href="manage_category.php" class="cancel-edit"><?= svg_icon('x', 12) ?> Cancel</a>
      </div>
    </div>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
      <input type="hidden" name="save_category" value="1">
      <?php if ($editData): ?>
        <input type="hidden" name="edit_mode" value="1">
        <input type="hidden" name="old_key" value="<?= htmlspecialchars($editData['group_key']) ?>">
      <?php endif; ?>

      <!-- Main Category Fields -->
      <div class="field-row-3">
        <div class="field-group">
          <label class="field-label">Category Slug <span class="field-hint">URL-friendly (e.g. \"footwear\")</span></label>
          <input type="text" name="group_key" value="<?= $editData ? htmlspecialchars($editData['group_key']) : '' ?>" placeholder="e.g. footwear" required>
          <div class="slug-preview">URL: category.php?cat=<span id="slugPreview"><?= $editData ? htmlspecialchars($editData['group_key']) : '...' ?></span></div>
        </div>
        <div class="field-group">
          <label class="field-label">Category Name <span class="field-hint">Display name (e.g. \"Footwear\")</span></label>
          <input type="text" name="label" value="<?= $editData ? htmlspecialchars($editData['label']) : '' ?>" placeholder="e.g. Footwear" required>
        </div>
        <div class="field-group">
          <label class="field-label">Icon (emoji)</label>
          <input type="text" name="icon" value="<?= $editData ? htmlspecialchars($editData['icon']) : '📁' ?>" placeholder="e.g. 👟" style="max-width:80px">
        </div>
      </div>

      <div class="field-row-3">
        <div class="field-group">
          <label class="field-label">Accent Color</label>
          <div class="color-input-wrap">
            <input type="color" name="color" value="<?= $editData ? htmlspecialchars($editData['color']) : '#6366F1' ?>">
            <input type="text" name="color_text" value="<?= $editData ? htmlspecialchars($editData['color']) : '#6366F1' ?>" placeholder="#6366F1" oninput="this.previousElementSibling.value=this.value">
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">Sort Order</label>
          <input type="number" name="sort_order" value="<?= $editData ? htmlspecialchars($editData['sort_order']) : ($totalGroups + 1) ?>" min="0" style="max-width:100px">
        </div>
      </div>

      <!-- Subcategories (up to 4) -->
      <div class="field-group" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--gray-200);">
        <label class="field-label">Subcategories <span class="field-hint">Select up to 4 sub-categories from existing product categories. Products with matching category will show on that sub-category page.</span></label>
        <div class="sub-repeater" id="subRepeater">
          <?php for ($i = 0; $i < 4; $i++): 
            $sub = $editData ? ($editData['subs'][$i] ?? null) : null;
            $sLabel = $sub ? $sub['label'] : '';
            $sKey   = $sub ? $sub['sub_key'] : '';
            $sIcon  = $sub ? $sub['icon'] : '📁';
          ?>
          <div class="sub-item">
            <div class="sub-num">Subcategory <?= $i + 1 ?></div>
            <div class="field-row">
              <div class="field-group" style="margin-bottom:6px">
                <label class="field-label" style="font-size:10px;">Select from Categories</label>
                <select name="sub_key[]" onchange="quickFillSub(this)">
                  <option value="">-- None --</option>
                  <?php foreach ($availableGroups as $ag): 
                    $selected = ($ag['group_key'] === $sKey) ? 'selected' : '';
                  ?>
                    <option value="<?= htmlspecialchars($ag['group_key']) ?>"
                            data-label="<?= htmlspecialchars($ag['label']) ?>"
                            data-icon="<?= htmlspecialchars($ag['icon']) ?>"
                            <?= $selected ?>>
                      <?= htmlspecialchars($ag['label']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field-group" style="margin-bottom:6px">
                <label class="field-label" style="font-size:10px;">Display Label</label>
                <input type="text" name="sub_label[]" value="<?= htmlspecialchars($sLabel) ?>" placeholder="e.g. Men's Shoes" class="sub-label-input">
              </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:2px;">
              <div>
                <label class="field-label" style="font-size:10px;">Icon</label>
                <input type="text" name="sub_icon[]" value="<?= htmlspecialchars($sIcon) ?>" placeholder="👟" style="max-width:70px" class="sub-icon-input">
              </div>
            </div>
          </div>
          <?php endfor; ?>
        </div>
        <div class="field-hint">Select a category from the dropdown. The slug maps directly to <code>category.php?cat=slug</code> on the frontend.</div>
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;padding-top:16px;border-top:1px solid var(--gray-200);">
        <button type="submit" class="btn-primary"><?= $editData ? svg_icon('save', 16).' Update Category' : svg_icon('plus', 16).' Create Category' ?></button>
        <a href="manage_category.php" class="btn-secondary">Cancel</a>
      </div>
    </form>
  </div>

  <!-- ── CATEGORY LIST ── -->
  <?php if (empty($allGroups)): ?>
    <div class="card" style="text-align:center;padding:60px 20px;">
      <div style="font-size:48px;margin-bottom:12px;"><?= svg_icon('folder', 48) ?></div>
      <div style="font-size:18px;font-weight:700;color:var(--gray-600);margin-bottom:6px;">No categories yet</div>
      <p style="font-size:14px;color:var(--gray-400);margin-bottom:20px;">Create your first category to start organizing products.</p>
      <button class="btn-primary" onclick="document.getElementById('addForm').style.display='block';this.closest('.card').scrollIntoView({behavior:'smooth'})"><?= svg_icon('plus', 16) ?> Add First Category</button>
    </div>
  <?php else: ?>
    <div class="cat-grid">
      <?php foreach ($allGroups as $gKey => $g): 
        $color = $g['color'] ?? '#6366F1';
      ?>
        <div class="cat-card">
          <div class="cat-card-header">
            <div class="cat-card-left">
              <div class="cat-card-icon" style="background:<?= htmlspecialchars($color) ?>15;color:<?= htmlspecialchars($color) ?>">
                <?= htmlspecialchars($g['icon'] ?? '📁') ?>
              </div>
              <div class="cat-card-info">
                <div class="cat-card-name"><?= htmlspecialchars($g['label']) ?></div>
                <div class="cat-card-key">Slug: <code style="background:var(--gray-100);padding:1px 5px;border-radius:3px;font-size:10px;"><?= htmlspecialchars($gKey) ?></code></div>
              </div>
            </div>
            <div class="cat-card-actions">
              <a href="?edit=<?= urlencode($gKey) ?>" class="btn-secondary btn-sm" style="padding:6px 10px;font-size:11px;"><?= svg_icon('edit', 13) ?></a>
              <a href="?delete=<?= urlencode($gKey) ?>&token=<?= $csrfToken ?>" class="btn-danger btn-sm" style="padding:6px 10px;font-size:11px;" onclick="return confirm('Delete category &quot;<?= htmlspecialchars($g['label']) ?>&quot;?')"><?= svg_icon('trash', 13) ?></a>
            </div>
          </div>
          <div class="cat-card-body">
            <?php if (empty($g['subs'])): ?>
              <div class="empty-subs">No subcategories</div>
            <?php else: ?>
              <div class="sub-list">
                <?php foreach ($g['subs'] as $si => $s): ?>
                  <div class="sub-list-item">
                    <span class="sub-list-icon"><?= htmlspecialchars($s['icon'] ?: '').($s['icon'] ? '' : svg_icon('bookmark', 14)) ?></span>
                    <span class="sub-list-label"><?= htmlspecialchars($s['label']) ?></span>
                    <span class="sub-list-key"><code><?= htmlspecialchars($s['sub_key']) ?></code></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- ── USAGE INFO ── -->
  <div class="card" style="padding:20px 28px;background:var(--off-white);border-style:dashed;margin-top:24px;">
    <div style="font-size:13px;font-weight:600;color:var(--gray-600);margin-bottom:8px;"><?= svg_icon('lightbulb', 16) ?> How it works</div>
    <ul style="font-size:13px;color:var(--gray-600);line-height:1.8;padding-left:18px;">
      <li><strong>Slug</strong> = URL-friendly name (e.g. "footwear"). Used in <code>category.php?cat=slug</code>.</li>
      <li><strong>Name</strong> = Display text shown on homepage cards and sidebar.</li>
      <li><strong>Subcategories</strong> — Select from existing product categories. Each subcategory becomes a clickable link on the homepage.</li>
      <li><strong>Homepage Layout</strong> — First 2 categories display as hero cards, next 4 as sub-category cards, remaining as more-category cards.</li>
      <li><strong>Sidebar</strong> — All categories appear in the category.php sidebar for navigation.</li>
      <li><strong>Data Source</strong> — All data is stored in the <code>admin_category</code> table in the specora database.</li>
    </ul>
  </div>

</div>

<!-- FOOTER -->
<footer style="background:var(--white);border-top:1px solid var(--gray-200);padding:20px 48px;text-align:center;font-size:13px;color:var(--gray-400);">
  <p>© 2025 <span style="color:var(--gold);">Specora</span> Admin Panel. All rights reserved.</p>
</footer>

</div><!-- /sidebar-page -->

<script>
// ── Live slug preview ──
document.querySelector('input[name="group_key"]')?.addEventListener('input', function() {
  document.getElementById('slugPreview').textContent = this.value || '...';
});

// ── Color sync ──
document.querySelector('input[name="color"]')?.addEventListener('input', function() {
  document.querySelector('input[name="color_text"]').value = this.value;
});

// ── Auto-fill label when subcategory dropdown changes ──
function quickFillSub(selectEl) {
  var option = selectEl.options[selectEl.selectedIndex];
  if (!option || !option.value) return;
  var item = selectEl.closest('.sub-item');
  if (!item) return;
  
  var labelInput = item.querySelector('.sub-label-input');
  var iconInput = item.querySelector('.sub-icon-input');
  
  if (labelInput) labelInput.value = option.dataset.label || '';
  if (iconInput) iconInput.value = option.dataset.icon || '📁';
}

// ── Auto-dismiss alerts ──
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => el.remove());
}, 6000);
</script>
</body>
</html>
