<?php
// ================================================
// category.php — Specora
// Shows products from database for a category
// Pagination: 20 products per page
// ================================================

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/functions.php';
if (!validate_db_connection($connect)) { die('<p style="font-family:sans-serif;padding:40px;text-align:center;color:#991B1B;">⚠️ Database connection is currently unavailable. Please try again later.</p>'); }

$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';

// ── Only redirect if BOTH cat and q are empty ──
if ($cat == '' && $search_q == '') {
    http_response_code(302);
    header('Location: index.php');
    exit;
}

// ── Detect if this is a generic search (no category, just search query) ──
$is_generic_search = ($cat === '' && $search_q !== '');

// ── Category display name & search term ──
$cat_search = str_replace('-', ' ', $cat);
$cat_safe = $cat_search !== '' ? mysqli_real_escape_string($connect, $cat_search) : '';
$displayName = $is_generic_search
    ? 'Search Results for &ldquo;' . htmlspecialchars($search_q) . '&rdquo;'
    : ucfirst($cat_search);

// ── Search params ──
$search_safe = $search_q !== '' ? mysqli_real_escape_string($connect, $search_q) : '';

// ── Build expanded search terms (only for category-based searches) ──
$expanded_search_terms = [];
if (!$is_generic_search) {
    $expanded_search_terms = [$cat_safe];
    $cat_slug_safe = mysqli_real_escape_string($connect, $cat);
    $adminGroupResult = mysqli_query($connect, "SELECT * FROM admin_category WHERE category_slug = '$cat_slug_safe' LIMIT 1");
    if ($adminGroupResult && $adminGroupRow = mysqli_fetch_assoc($adminGroupResult)) {
        $displayName = $adminGroupRow['category_name'];
        for ($si = 1; $si <= 4; $si++) {
            $subName = $adminGroupRow["sub{$si}_name"] ?? '';
            if ($subName !== '') {
                $expanded_search_terms[] = mysqli_real_escape_string($connect, $subName);
            }
        }
    }
}

// ── Pagination ──
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// ── Detect marketplace keywords in search ──
$marketplace_keywords = array('amazon', 'aliexpress', 'ebay', 'walmart');
$detected_marketplace = '';

// ── Smart search filters (price & rating) ──
$search_price_min = 0.0;
$search_price_max = 0.0;
$search_rating_min = 0.0;

// ── Build WHERE clause ──
$where_base = '1=1';

if (!$is_generic_search && !empty($expanded_search_terms)) {
    // Category-based search: find products matching ANY expanded term (parent + sub-categories)
    $where_conditions = [];
    foreach ($expanded_search_terms as $term) {
        $term_safe = mysqli_real_escape_string($connect, $term);
        $where_conditions[] = "(c.category_name LIKE '%$term_safe%' OR p.search_query LIKE '%$term_safe%')";
    }
    $where_base = '(' . implode(' OR ', $where_conditions) . ')';
}

if ($search_safe !== '') {
    // ══ SMARTER SEARCH: Split & parse words for price/rating/text ══
    $search_words = explode(' ', $search_safe);
    $filtered_words = array();

    // ── First pass: marketplaces & smart filters ──
    foreach ($search_words as $word) {
        $word = trim($word);
        if ($word === '') continue;
        $word_lower = strtolower($word);

        // Marketplace keyword?
        if (in_array($word_lower, $marketplace_keywords)) {
            $detected_marketplace = $word_lower;
            continue;
        }

        $filtered_words[] = $word;
    }

    // ── Second pass: detect price & rating patterns ──
    $rebuilt = array();
    $i = 0;
    while ($i < count($filtered_words)) {
        $w = $filtered_words[$i];
        $wl = strtolower($w);
        $matched = false;

        // "under $X" / "under X" / "below $X" / "max $X" / "budget $X"
        if (in_array($wl, ['under','below','less','max','budget','upto','up','within'])
            && isset($filtered_words[$i+1])) {
            $nxt = $filtered_words[$i+1];
            $num = preg_replace('/[^0-9.]/', '', $nxt);
            if (is_numeric($num) && floatval($num) > 0) {
                $search_price_max = floatval($num);
                $i += ($wl === 'up' && isset($filtered_words[$i+1]) && strtolower($filtered_words[$i+1]) === 'to') ? 3 : 2;
                $matched = true;
            }
        }

        // "above $X" / "over $X" / "min $X" / "minimum $X" / "more than $X"
        if (!$matched && in_array($wl, ['above','over','min','minimum','more','starting','from'])
            && isset($filtered_words[$i+1])) {
            $nxt = $filtered_words[$i+1];
            $num = preg_replace('/[^0-9.]/', '', $nxt);
            if (is_numeric($num) && floatval($num) > 0) {
                $search_price_min = floatval($num);
                $skip = 2;
                if ($wl === 'more' && isset($filtered_words[$i+1]) && strtolower($filtered_words[$i+1]) === 'than') $skip = 3;
                $i += $skip;
                $matched = true;
            }
        }

        // "<$50" / "<50" / ">$50" / ">50"
        if (!$matched && preg_match('/^([<>])\$?(\d+(\.\d+)?)$/', $w, $m)) {
            $val = floatval($m[2]);
            if ($val > 0) {
                if ($m[1] === '<') $search_price_max = $val;
                else $search_price_min = $val;
                $i++;
                $matched = true;
            }
        }

        // "$50" standalone (treat as approx price — requires $ prefix so "iphone 15" isn't treated as price)
        if (!$matched && preg_match('/^\$(\d+(\.\d+)?)$/', $w, $m)) {
            $val = floatval($m[1]);
            if ($val > 0 && $val < 10000) {
                $search_price_min = $val * 0.8;
                $search_price_max = $val * 1.2;
                $i++;
                $matched = true;
            }
        }

        // "rating X" / "rated X" / "reviews X"
        if (!$matched && in_array($wl, ['rating','rated','reviews','review'])
            && isset($filtered_words[$i+1])) {
            $nxt = $filtered_words[$i+1];
            $num = preg_replace('/[^0-9.]/', '', $nxt);
            if (is_numeric($num) && floatval($num) >= 1 && floatval($num) <= 5) {
                $search_rating_min = floatval($num);
                $i += 2;
                $matched = true;
            }
        }

        // "X star" / "X stars"
        if (!$matched && isset($filtered_words[$i+1])) {
            $nxtl = strtolower($filtered_words[$i+1]);
            if (($nxtl === 'star' || $nxtl === 'stars') && is_numeric($w)) {
                $rv = floatval($w);
                if ($rv >= 1 && $rv <= 5) {
                    $search_rating_min = $rv;
                    $i += 2;
                    $matched = true;
                }
            }
        }

        // "X dollars" / "X USD" / "X dollar"
        if (!$matched && isset($filtered_words[$i+1])) {
            $nxtl = strtolower($filtered_words[$i+1]);
            if (in_array($nxtl, ['dollars','usd','dollar','$']) && is_numeric($w)) {
                $val = floatval($w);
                if ($val > 0 && $val < 100000) {
                    $search_price_min = $val * 0.8;
                    $search_price_max = $val * 1.2;
                    $i += 2;
                    $matched = true;
                }
            }
        }

        if (!$matched) {
            $rebuilt[] = $w;
            $i++;
        }
    }
    $filtered_words = $rebuilt;

    // ── Build text-search conditions from remaining words ──
    $word_conditions = array();
    foreach ($filtered_words as $word) {
        $word_conditions[] = "(p.title LIKE '%$word%' OR p.search_query LIKE '%$word%')";
    }

    if (!empty($word_conditions)) {
        $word_sql = implode(' AND ', $word_conditions);
        if ($is_generic_search) {
            $where_base = "($word_sql)";
        } else {
            $where_base = "(c.category_name LIKE '%$cat_safe%' OR p.search_query LIKE '%$cat_safe%') AND ($word_sql)";
        }
    }
}

// ── Append price / rating smart filters ──
$smart_conditions = [];
if ($search_price_min > 0) {
    $smart_conditions[] = "p.price_usd >= $search_price_min";
}
if ($search_price_max > 0) {
    $smart_conditions[] = "p.price_usd <= $search_price_max";
}
if ($search_rating_min > 0) {
    $smart_conditions[] = "p.rating >= $search_rating_min";
}
if (!empty($smart_conditions)) {
    $where_base .= ' AND ' . implode(' AND ', $smart_conditions);
}

// ── Generate smart filter labels for display ──
$filter_tags = array();
if ($search_price_min > 0 && $search_price_max > 0) {
    $filter_tags[] = '$' . number_format($search_price_min, $search_price_min == round($search_price_min) ? 0 : 2) . ' &ndash; $' . number_format($search_price_max, $search_price_max == round($search_price_max) ? 0 : 2);
} elseif ($search_price_max > 0) {
    $filter_tags[] = 'Under $' . number_format($search_price_max, $search_price_max == round($search_price_max) ? 0 : 2);
} elseif ($search_price_min > 0) {
    $filter_tags[] = 'From $' . number_format($search_price_min, $search_price_min == round($search_price_min) ? 0 : 2);
}
if ($search_rating_min > 0) {
    $filter_tags[] = '★ ' . number_format($search_rating_min, 1) . '+ Rating';
}
if ($detected_marketplace !== '') {
    $filter_tags[] = ucfirst($detected_marketplace);
}

// ── Sort logic ──
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'RELEVANCE';
$order_clause = '';
switch ($sort) {
    case 'PRICE_LOW_TO_HIGH':
        $order_clause = 'price_usd ASC';
        break;
    case 'PRICE_HIGH_TO_LOW':
        $order_clause = 'price_usd DESC';
        break;
    case 'NEWEST':
        $order_clause = 'saved_at DESC';
        break;
    case 'OLDEST':
        $order_clause = 'saved_at ASC';
        break;
    default:
        // RELEVANCE: priority match first, then detected marketplace
        $priority_sql = "CASE WHEN search_query LIKE '%$cat_safe%' THEN 0 ELSE 1 END";
        if ($detected_marketplace !== '') {
            $priority_sql = "CASE WHEN source = '$detected_marketplace' THEN 0 ELSE 1 END, $priority_sql";
        }
        $order_clause = $priority_sql;
        break;
}

// ── Count total products ──
function countCategoryProducts($connect, $where_base) {
    $count_query = "
        SELECT COUNT(*) as total FROM (
            SELECT p.product_id AS id FROM aliexpress_products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_base AND p.price_usd >= 1
            UNION ALL
            SELECT p.asin AS id FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_base
            UNION ALL
            SELECT p.item_id AS id FROM walmart_products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_base AND p.price_usd >= 1
            UNION ALL
            SELECT p.item_id AS id FROM ebay_products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $where_base
        ) merged
    ";
    $count_result = mysqli_query($connect, $count_query);
    $count_row    = mysqli_fetch_assoc($count_result);
    return (int)($count_row['total'] ?? 0);
}

$totalProducts = countCategoryProducts($connect, $where_base);

$totalPages = max(1, ceil($totalProducts / $perPage));

// ── Fetch products for current page ──
$product_query = "
    SELECT * FROM (
        SELECT
            p.title, p.brand, p.category_id, p.price_usd, p.price_pkr,
            p.rating, p.num_ratings, p.photo, p.affiliate_link, p.search_query,
            p.saved_at, 'aliexpress' AS source, p.product_id AS asin
        FROM aliexpress_products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_base AND p.price_usd >= 1
        UNION ALL
        SELECT
            p.title, p.brand, p.category_id, p.price_usd, p.price_pkr,
            p.rating, p.num_ratings, p.photo, p.affiliate_link, p.search_query,
            p.saved_at, 'amazon' AS source, p.asin
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_base
        UNION ALL
        SELECT
            p.title, p.brand, p.category_id, p.price_usd, p.price_pkr,
            p.rating, p.num_ratings, p.photo, p.affiliate_link, p.search_query,
            p.saved_at, 'walmart' AS source, p.item_id AS asin
        FROM walmart_products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_base AND p.price_usd >= 1
        UNION ALL
        SELECT
            p.title, p.brand, p.category_id, p.price_usd, p.price_pkr,
            p.rating, p.num_ratings, p.photo, p.affiliate_link, p.search_query,
            p.saved_at, 'ebay' AS source, p.item_id AS asin
        FROM ebay_products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_base
    ) merged
    ORDER BY $order_clause
    LIMIT $perPage OFFSET $offset
";
$product_result = mysqli_query($connect, $product_query);

$all = array();
while ($row = mysqli_fetch_assoc($product_result)) $all[] = $row;

// ══ FUZZY / FALLBACK SEARCH — when exact search yields 0 results ══
$did_you_mean = '';

if (count($all) === 0 && $search_safe !== '') {
    $fallback_words = $filtered_words ?? [];
    
    // ── Strategy 1: Try OR matching (any word, not all) + character removal ──
    $fuzzy_word_sets = [];
    $fuzzy_word_sets[] = $fallback_words; // original words first
    
    // Generate variants: remove last 1 char, remove last 2 chars
    foreach ($fallback_words as $fw) {
        $variants = [$fw];
        if (strlen($fw) > 3) $variants[] = substr($fw, 0, -1);
        if (strlen($fw) > 4) $variants[] = substr($fw, 0, -2);
        // Truncate long words for broader match
        if (strlen($fw) > 6) $variants[] = substr($fw, 0, -3);
        $fuzzy_word_sets[] = array_unique($variants);
    }
    
    // Try each variant set until we get results
    foreach ($fuzzy_word_sets as $variant_idx => $word_set) {
        if (count($all) > 0) break;
        
        // Build OR conditions from this word set (any word matching is enough)
        $fuzzy_conditions = [];
        foreach ($word_set as $fw) {
            $fw_safe = mysqli_real_escape_string($connect, $fw);
            $fuzzy_conditions[] = "(p.title LIKE '%$fw_safe%' OR p.search_query LIKE '%$fw_safe%')";
        }
        
        if (empty($fuzzy_conditions)) continue;
        
        $fuzzy_sql = '(' . implode(' OR ', $fuzzy_conditions) . ')';
        
        // Preserve category condition if applicable
        if (!$is_generic_search && $cat_safe !== '') {
            $fuzzy_sql = "(c.category_name LIKE '%$cat_safe%' OR p.search_query LIKE '%$cat_safe%') AND ($fuzzy_sql)";
        }
        
        // Build fallback query (no price/rating filters, and more relaxed)
        $fallback_query = str_replace($where_base, $fuzzy_sql, $product_query);
        
        $fb_result = mysqli_query($connect, $fallback_query);
        if ($fb_result) {
            $fb_rows = [];
            while ($fb_r = mysqli_fetch_assoc($fb_result)) $fb_rows[] = $fb_r;
            if (count($fb_rows) > 0) {
                $all = array_slice($fb_rows, 0, $perPage);
                // Generate "did you mean" from the variant words that worked
                if ($variant_idx > 0) {
                    $original_flat = implode(' ', $fallback_words);
                    $variant_flat = implode(' ', $word_set);
                    if ($variant_flat !== $original_flat) {
                        $did_you_mean = $variant_flat;
                    }
                }
            }
        }
    }
    
    // ── Strategy 3: Still nothing? Try SOUNDEX phonetic matching ──
    if (count($all) === 0 && !empty($fallback_words)) {
        $soundex_conditions = [];
        foreach ($fallback_words as $fw) {
            $fw_safe = mysqli_real_escape_string($connect, $fw);
            // SOUNDEX matches words that sound similar
            $soundex_conditions[] = "SOUNDEX(p.title) LIKE CONCAT(SOUNDEX('$fw_safe'), '%')";
            // Also try shorter version for broader match
            if (strlen($fw_safe) > 3) {
                $short = substr($fw_safe, 0, -1);
                $soundex_conditions[] = "SOUNDEX(p.title) LIKE CONCAT(SOUNDEX('$short'), '%')";
            }
        }
        if (!empty($soundex_conditions)) {
            $soundex_sql = '(' . implode(' OR ', $soundex_conditions) . ')';
            if (!$is_generic_search && $cat_safe !== '') {
                $soundex_sql = "(c.category_name LIKE '%$cat_safe%' OR p.search_query LIKE '%$cat_safe%') AND ($soundex_sql)";
            }
            $fallback_query2 = str_replace($where_base, $soundex_sql, $product_query);
            $fb_result2 = mysqli_query($connect, $fallback_query2);
            if ($fb_result2) {
                $fb_rows2 = [];
                while ($fb_r2 = mysqli_fetch_assoc($fb_result2)) $fb_rows2[] = $fb_r2;
                if (count($fb_rows2) > 0) {
                    $all = array_slice($fb_rows2, 0, $perPage);
                }
            }
        }
    }
    
    // ── Strategy 4: Absolute last resort — show popular products from the category ──
    if (count($all) === 0) {
        $last_query = str_replace($where_base, '1=1', $product_query);
        // Add a LIMIT to get some products
        $last_query = preg_replace('/LIMIT \d+ OFFSET \d+/', 'LIMIT 20', $last_query);
        $fb_result3 = mysqli_query($connect, $last_query);
        if ($fb_result3) {
            $fb_rows3 = [];
            while ($fb_r3 = mysqli_fetch_assoc($fb_result3)) $fb_rows3[] = $fb_r3;
            if (count($fb_rows3) > 0) {
                $all = array_slice($fb_rows3, 0, $perPage);
            }
        }
    }
    
    // Recalculate pagination for fallback results
    if (count($all) > 0) {
        $totalProducts = count($all);
        $totalPages = 1;
    }
}

// ── Fetch all sub-categories for sidebar ──
$sidebar_groups = mysqli_query($connect, "SELECT * FROM admin_category ORDER BY sort_order ASC, created_at ASC");
$sidebar_items = array();
if ($sidebar_groups && mysqli_num_rows($sidebar_groups) > 0) {
    while ($sg = mysqli_fetch_assoc($sidebar_groups)) {
        $group_name = $sg['category_name'];
        $group_slug = $sg['category_slug'];
        $subs = array();
        for ($si = 1; $si <= 4; $si++) {
            $sn = $sg["sub{$si}_name"] ?? '';
            $ss = $sg["sub{$si}_slug"] ?? '';
            if ($sn !== '') {
                $subs[] = array('name' => $sn, 'slug' => $ss);
            }
        }
        $sidebar_items[] = array(
            'group_name' => $group_name,
            'group_slug' => $group_slug,
            'subs' => $subs
        );
    }
} else {
    $sc2 = mysqli_query($connect, "SELECT DISTINCT category_name FROM categories WHERE category_name != '' ORDER BY category_name ASC LIMIT 20");
    if ($sc2 && mysqli_num_rows($sc2) > 0) {
        while ($sc = mysqli_fetch_assoc($sc2)) {
            $name = $sc['category_name'];
            $slug = strtolower(trim($name));
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
            $sidebar_items[] = array(
                'group_name' => $name,
                'group_slug' => $slug,
                'subs' => array(array('name' => $name, 'slug' => $slug))
            );
        }
    }
}

// ── Build pagination URL ──
if ($is_generic_search) {
    $pageUrl = 'category.php?q=' . urlencode($search_q);
} else {
    $pageUrl = 'category.php?cat=' . urlencode($cat);
    if ($search_q !== '') $pageUrl .= '&q=' . urlencode($search_q);
}
if ($sort !== 'RELEVANCE') $pageUrl .= '&sort=' . urlencode($sort);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($displayName); ?> — Specora</title>
<meta name="description" content="Browse <?= htmlspecialchars($displayName) ?> products on Specora. Compare prices, ratings, and features from Amazon, AliExpress, eBay &amp; Walmart.">
<meta name="keywords" content="<?= htmlspecialchars(strtolower($displayName)) ?>, product comparison, price compare, shopping Pakistan">
<meta property="og:title" content="<?= htmlspecialchars($displayName); ?> — Specora">
<meta property="og:description" content="Browse <?= htmlspecialchars($displayName) ?> products on Specora. Compare prices, ratings, and features from Amazon, AliExpress, eBay &amp; Walmart.">
<meta property="og:image" content="img/specora-logo.png">
<meta property="og:url" content="https://specora.pk/category.php?cat=<?= htmlspecialchars($cat) ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($displayName); ?> — Specora">
<meta name="twitter:description" content="Browse <?= htmlspecialchars($displayName) ?> products on Specora. Compare prices, ratings, and features from Amazon, AliExpress, eBay &amp; Walmart.">
<meta name="twitter:image" content="img/specora-logo.png">
<link rel="icon" type="image/svg+xml" href="img/favicon.svg">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/navbar.css?v=<?= filemtime(__DIR__.'/css/navbar.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--gold:#EABD78;--gold-light:#F5D9A8;--gold-dark:#C9973A;--black:#111111;--white:#FFFFFF;--off-white:#FAFAF8;--gray-100:#F4F3F0;--gray-200:#E8E6E0;--gray-400:#A8A49C;--gray-600:#6B6760;--font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--radius:14px;--radius-pill:100px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{font-family:var(--font-body);background:var(--white);color:var(--black);display:flex;flex-direction:column;min-height:100vh;}
a{text-decoration:none;color:inherit;}



.main-content{width:100%;padding:48px 32px 72px;flex:1;display:flex;gap:0;align-items:flex-start;}

/* ── SIDEBAR ── */
.cat-sidebar{width:280px;flex-shrink:0;background:var(--white);border-right:1px solid var(--gray-200);position:sticky;top:20px;max-height:calc(100vh - 40px);overflow-y:auto;padding-bottom:20px;}
.cat-sidebar-header{display:flex;align-items:center;justify-content:space-between;padding:18px 16px 14px;border-bottom:1px solid var(--gray-200);}
.cat-sidebar-header span{font-family:var(--font-display);font-size:14px;font-weight:800;color:var(--black);letter-spacing:-.3px;}
.cat-sidebar-close{display:none;width:28px;height:28px;border-radius:50%;background:var(--gray-100);border:none;font-size:12px;cursor:pointer;align-items:center;justify-content:center;transition:all .2s;color:var(--gray-600);}
.cat-sidebar-close:hover{background:var(--gray-200);color:var(--black);}

/* ── SIDEBAR ACCORDION GROUPS ── */
.cat-sidebar-groups{padding:8px 0;}
.cat-sidebar-group{margin-bottom:4px;}
.cat-sidebar-group:last-child{margin-bottom:0;}
.cat-sidebar-group + .cat-sidebar-group{border-top:1px solid var(--gray-200);padding-top:6px;margin-top:6px;}
.cat-sidebar-group-header{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;transition:all .2s;border-radius:0;}
.cat-sidebar-group-header:hover{background:var(--gray-100);}
.cat-sidebar-group-header.active{background:rgba(234,189,120,.08);}
.cat-sidebar-group-link{font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);letter-spacing:-.2px;text-decoration:none;flex:1;padding:6px 0;transition:color .2s;}
.cat-sidebar-group-link:hover{color:var(--gold-dark);}
.cat-sidebar-toggle{width:28px;height:28px;border-radius:50%;border:2px solid var(--gray-200);background:var(--white);color:var(--gray-400);font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .25s cubic-bezier(.34,1.56,.64,1);flex-shrink:0;padding:0;line-height:1;}
.cat-sidebar-toggle:hover{border-color:var(--gold-dark);color:var(--gold-dark);background:rgba(234,189,120,.06);}
.cat-sidebar-toggle.open{transform:rotate(45deg);border-color:var(--gold-dark);color:var(--gold-dark);background:rgba(234,189,120,.1);}
.cat-sidebar-sub-list{max-height:0;overflow:hidden;transition:max-height .35s ease,opacity .25s ease, margin .25s ease;opacity:0;margin:0;}
.cat-sidebar-sub-list.open{max-height:400px;opacity:1;margin:2px 0 4px;}
.cat-sidebar-link{display:flex;align-items:center;gap:10px;padding:9px 16px 9px 28px;font-size:13px;font-weight:500;color:var(--gray-600);transition:all .15s;text-decoration:none;border-left:3px solid transparent;border-radius:0;}
.cat-sidebar-link:hover{background:var(--gray-100);color:var(--black);border-left-color:var(--gold);}
.cat-sidebar-link.active{background:rgba(234,189,120,.1);color:var(--gold-dark);font-weight:700;border-left-color:var(--gold-dark);}
.cat-sidebar-link .cat-sidebar-bullet{width:4px;height:4px;border-radius:50%;background:var(--gray-400);flex-shrink:0;transition:all .2s;}
.cat-sidebar-link:hover .cat-sidebar-bullet{background:var(--gold);}

/* ── MAIN CONTENT AREA ── */
.cat-content{flex:1;min-width:0;padding-left:16px;}
.products-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;}
.product-card{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;transition:transform .25s,box-shadow .25s;}
.product-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.09);}
.product-img{width:100%;aspect-ratio:1;overflow:hidden;background:var(--gray-100);position:relative;}
.product-img img{width:100%;height:100%;object-fit:cover;transition:transform .3s;}
.product-card:hover .product-img img{transform:scale(1.05);}
.product-body{padding:14px;}
.product-brand{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gray-400);margin-bottom:4px;}
.product-name{font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--black);margin-bottom:6px;line-height:1.3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;}
.product-rating{font-size:11px;color:var(--gray-400);margin-bottom:8px;}
.product-rating span{color:var(--gold-dark);}
.product-footer{display:flex;align-items:center;justify-content:space-between;}
.product-price{font-size:15px;font-weight:700;color:var(--black);}
.product-price sup{font-size:10px;}


/* ── SUGGESTIONS DROPDOWN in filter bar ── */
.fb-search{position:relative;}
.fb-search .search-suggestions{position:absolute;top:100%;left:0;right:0;z-index:999;background:var(--white);border:2px solid var(--black);border-top:none;box-shadow:0 8px 24px rgba(0,0,0,.12);display:none;max-height:360px;overflow-y:auto;}
.fb-search .search-suggestions.active{display:block;}
.fb-search .suggestion-item{display:flex;align-items:center;gap:10px;padding:10px 14px;cursor:pointer;transition:background .15s;font-size:13px;color:var(--gray-600);}
.fb-search .suggestion-item:hover{background:var(--gray-100);color:var(--black);}
.fb-search .suggestion-icon{width:28px;height:28px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;}
.fb-search .suggestion-text{font-weight:500;}

/* ── FILTER BAR ── */
.filter-bar{background:var(--white);border-bottom:1px solid var(--gray-200);padding:16px 32px;position:relative;}
.filter-bar form{display:flex;align-items:center;gap:12px;justify-content:flex-end;flex-wrap:wrap;}
.fb-cat-btn{display:none;align-items:center;gap:6px;padding:10px 16px;background:var(--gray-100);border:2px solid var(--black);font-family:var(--font-body);font-size:13px;font-weight:600;color:var(--gray-600);cursor:pointer;transition:all .2s;white-space:nowrap;}
.fb-cat-btn:hover{background:var(--black);color:var(--white);}
.fb-cat-btn.active{background:var(--black);color:var(--white);}
.filter-bar .fb-search{flex:1 1 auto;max-width:700px;min-width:200px;position:relative;}@media(min-width:1200px){.filter-bar .fb-search{max-width:none;}}
.filter-bar .fb-search input{width:100%;padding:10px 16px;border:2px solid var(--black);font-family:var(--font-body);font-size:14px;outline:none;background:var(--white);transition:all .2s;}
.filter-bar .fb-search input:focus{border-color:var(--gold-dark);}
.filter-bar .fb-sort{padding:10px 16px;border:2px solid var(--black);font-family:var(--font-body);font-size:13px;font-weight:500;outline:none;background:var(--white);cursor:pointer;transition:all .2s;min-width:150px;max-width:200px;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23111' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:32px;}
.filter-bar .fb-sort:focus{border-color:var(--gold-dark);}
.filter-bar select:focus{border-color:var(--gold-dark);}
.filter-bar .fb-btn{padding:10px 20px;background:var(--black);color:var(--white);border:none;font-size:13px;font-weight:700;font-family:var(--font-display);cursor:pointer;transition:all .2s;white-space:nowrap;}
.filter-bar .fb-btn:hover{background:var(--gold-dark);}
@media(max-width:768px){.filter-bar{padding:12px 20px;}.filter-bar form{justify-content:stretch;}.fb-cat-btn{display:flex;order:-1;}.filter-bar .fb-search{flex:1 1 100%;max-width:100%;min-width:0;order:0;}.filter-bar select{flex:1;min-width:0;max-width:none;}.cat-sidebar{display:none;}.cat-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:499;backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);}.cat-sidebar-overlay.open{display:block;}.cat-sidebar.mobile-open{display:block;position:fixed;top:0;left:0;bottom:0;width:280px;background:var(--white);z-index:500;overflow-y:auto;box-shadow:4px 0 30px rgba(0,0,0,.15);padding:0;animation:slideInLeft .3s ease;}@media(max-width:480px){.cat-sidebar.mobile-open{top:0;}}.cat-sidebar-header{display:flex;padding:20px 16px 14px;border-bottom:1px solid var(--gray-200);}.cat-sidebar-header span{font-size:16px;}.cat-sidebar-close{display:flex;width:32px;height:32px;font-size:14px;}.cat-sidebar-groups{padding:4px 0;}.cat-sidebar-group-header{padding:14px 20px;}.cat-sidebar-group-link{font-size:15px;padding:4px 0;}.cat-sidebar-toggle{width:30px;height:30px;font-size:15px;}.cat-sidebar-sub-list.open{padding:2px 0 6px;}.cat-sidebar-link{padding:12px 20px 12px 28px;font-size:14px;border-left-width:4px;}.cat-sidebar-link:last-child{margin-bottom:0;}}

@keyframes slideInLeft{from{transform:translateX(-100%);}to{transform:translateX(0);}}

/* ── DID YOU MEAN / FALLBACK ── */
.did-you-mean{background:rgba(234,189,120,.1);border:1.5px solid var(--gold);border-radius:var(--radius-pill);padding:10px 20px;margin-bottom:20px;font-size:14px;color:var(--gray-600);display:inline-flex;align-items:center;gap:10px;}
.did-you-mean strong{color:var(--black);font-weight:700;}
.did-you-mean .dym-link{color:var(--gold-dark);font-weight:700;text-decoration:underline;cursor:pointer;transition:all .2s;}
.did-you-mean .dym-link:hover{color:var(--black);}
/* ── SMART FILTER TAGS ── */
.filter-tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:20px;padding:0 0 8px 0;}
.filter-tag{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--gray-100);border:1.5px solid var(--gray-200);border-radius:var(--radius-pill);font-size:12px;font-weight:600;color:var(--gray-600);transition:all .2s;}
.filter-tag:hover{border-color:var(--gold-dark);color:var(--black);}
.filter-tag .tag-icon{font-size:12px;flex-shrink:0;}
.filter-tag .tag-label{font-family:var(--font-display);}


/* ── PAGINATION ── */
.pagination{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:40px;flex-wrap:wrap;}
.page-num{display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 10px;border:1.5px solid var(--gray-200);border-radius:10px;font-size:13px;font-weight:600;color:var(--gray-600);background:var(--white);transition:all .2s;text-decoration:none;}
.page-num:hover{border-color:var(--gold);color:var(--black);}
.page-num.active{background:var(--gold);border-color:var(--gold-dark);color:var(--black);font-weight:700;}
.page-arrow{font-size:16px;padding:0 8px;}

.no-results{text-align:center;padding:80px 20px;color:var(--gray-400);}
.no-results div{font-size:48px;margin-bottom:16px;}
.no-results p{font-size:15px;line-height:1.6;}

@media(max-width:1200px){.products-grid{grid-template-columns:repeat(4,1fr);gap:14px;}}
@media(max-width:1024px){.products-grid{grid-template-columns:repeat(3,1fr);gap:14px;}.cat-content{padding:0 0 0 24px;}}
@media(max-width:1024px){.main-content{padding:28px 24px 56px;}.cat-sidebar{width:220px;}.cat-content{padding:0 0 0 20px;}}

@media(max-width:768px){.main-content{flex-direction:column;padding:0 0 48px;gap:0;}.cat-content{padding:24px 16px 0;}}
@media(max-width:480px){.products-grid{grid-template-columns:repeat(2,1fr);gap:8px;}.product-card{border-radius:10px;}.product-img{aspect-ratio:1;max-height:140px;}.product-body{padding:8px 10px;}.product-brand{font-size:9px;margin-bottom:2px;letter-spacing:1px;}.product-name{font-size:12px;margin-bottom:4px;display:-webkit-box;-webkit-line-clamp:2;}.product-rating{font-size:10px;margin-bottom:4px;}.product-price{font-size:13px;}.pagination{gap:4px;}.page-num{min-width:32px;height:32px;font-size:11px;}.cat-content{padding:20px 16px 0;}}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>



<!-- FILTER BAR -->
<div class="filter-bar">
  <form method="get" style="display:flex;align-items:center;gap:12px;width:100%;flex-wrap:wrap;">
    <?php if (!$is_generic_search): ?>
    <input type="hidden" name="cat" value="<?= htmlspecialchars($cat); ?>"/>
    <?php endif; ?>
    <button type="button" class="fb-cat-btn" id="fbCatBtn" onclick="toggleMobileSidebar()">☰ Categories</button>
    <div class="fb-search">
      <input type="text" name="q" id="fbSearchInput" placeholder="Search your product..." value="<?= htmlspecialchars($search_q); ?>" autocomplete="off"/>
      <div class="search-suggestions" id="fbSearchSuggestions"></div>
    </div>
    <select name="sort" class="fb-sort" onchange="this.form.submit()">
      <option value="RELEVANCE" <?= $sort === 'RELEVANCE' ? 'selected' : '' ?>>Sort: Relevance</option>
      <option value="PRICE_HIGH_TO_LOW" <?= $sort === 'PRICE_HIGH_TO_LOW' ? 'selected' : '' ?>>High to Low</option>
      <option value="PRICE_LOW_TO_HIGH" <?= $sort === 'PRICE_LOW_TO_HIGH' ? 'selected' : '' ?>>Low to High</option>
      <option value="NEWEST" <?= $sort === 'NEWEST' ? 'selected' : '' ?>>Newly Added</option>
      <option value="OLDEST" <?= $sort === 'OLDEST' ? 'selected' : '' ?>>Old</option>
    </select>
    <button type="submit" class="fb-btn">Search</button>
    <?php if ($search_q !== ''): ?>
    <a href="<?= $is_generic_search ? 'index.php' : 'category.php?cat=' . htmlspecialchars($cat); ?>" style="font-size:13px;color:var(--gray-400);text-decoration:none;white-space:nowrap;">Clear ✕</a>
    <?php endif; ?>
  </form>
</div>

<div class="main-content">
  <!-- OVERLAY for mobile sidebar -->
  <div class="cat-sidebar-overlay" id="catSidebarOverlay" onclick="closeMobileSidebar()"></div>
  
  <!-- SIDEBAR (mobile side drawer) -->
  <aside class="cat-sidebar" id="catSidebar">
    <div class="cat-sidebar-header">
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;vertical-align:middle;margin-right:6px;"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg> Categories</span>
      <button class="cat-sidebar-close" onclick="closeMobileSidebar()">✕</button>
    </div>    <div class="cat-sidebar-groups">
      <?php foreach ($sidebar_items as $sg):
        $sg_name = htmlspecialchars($sg['group_name']);
        $sg_slug = htmlspecialchars($sg['group_slug']);
        // Check if any sub-category in this group matches current category
        $is_group_active = false;
        foreach ($sg['subs'] as $sc) {
          if ($sc['slug'] === $cat) { $is_group_active = true; break; }
        }
      ?>
      <div class="cat-sidebar-group">
        <div class="cat-sidebar-group-header <?= $is_group_active ? 'active' : '' ?>">
          <a href="category.php?cat=<?= $sg_slug ?>" class="cat-sidebar-group-link"><?= $sg_name ?></a>
          <button class="cat-sidebar-toggle <?= $is_group_active ? 'open' : '' ?>" onclick="event.stopPropagation(); toggleSidebarGroup(this)">+</button>
        </div>
        <div class="cat-sidebar-sub-list <?= $is_group_active ? 'open' : '' ?>">
          <?php foreach ($sg['subs'] as $sc):
            $sc_slug = htmlspecialchars($sc['slug']);
            $sc_name = htmlspecialchars($sc['name']);
            $sc_active = ($sc_slug === $cat) ? ' active' : '';
          ?>
          <a href="category.php?cat=<?= $sc_slug ?>" class="cat-sidebar-link<?= $sc_active ?>">
            <span class="cat-sidebar-bullet"></span>
            <?= $sc_name ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- PRODUCTS -->
  <div class="cat-content">
  <?php if (count($all) > 0): ?>
  <?php if (!empty($filter_tags)): ?>
  <div class="filter-tags">
    <?php foreach ($filter_tags as $ft): ?>
    <span class="filter-tag">
      <span class="tag-icon">⚡</span>
      <span class="tag-label"><?= $ft; ?></span>
    </span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  
  <?php if ($did_you_mean !== ''): 
    // Build a clean URL with the suggested query (replace original q param)
    $dymUrl = $is_generic_search
        ? 'category.php?q=' . urlencode($did_you_mean)
        : 'category.php?cat=' . urlencode($cat) . '&q=' . urlencode($did_you_mean);
  ?>
  <div class="did-you-mean">
    <span>🤔</span>
    <span>Did you mean: <strong><a href="<?= $dymUrl; ?>" class="dym-link"><?= htmlspecialchars($did_you_mean); ?></a></strong>?</span>
  </div>
  <?php endif; ?>
  
  
  <div class="products-grid">
    <?php foreach ($all as $p):
      $title = substr($p['title'] ?? '', 0, 70) . (strlen($p['title'] ?? '') > 70 ? '…' : '');
      $price = $p['price_usd'] > 0 ? '<sup>$</sup>' . number_format($p['price_usd'], 2) : 'Price N/A';
      $photo = $p['photo'] ?: 'https://placehold.co/300x300/F4F3F0/A8A49C?text=No+Image';
      $rating = floatval($p['rating'] ?? 0);
      $numRat = intval($p['num_ratings'] ?? 0);
      $source = $p['source'] ?? 'aliexpress';
      $asin = $p['asin'] ?? '';
      $brand = $p['brand'] ?: 'Specora';
      $detailUrl = 'product.php?asin=' . urlencode($asin) . '&source=' . urlencode($source);
    ?>
    <a href="<?= $detailUrl; ?>" class="product-card">
      <div class="product-img">
        <img src="<?= htmlspecialchars($photo); ?>" alt="<?= htmlspecialchars($title); ?>" loading="lazy" onerror="this.src='https://placehold.co/300x300/F4F3F0/A8A49C?text=No+Image'"/>
      </div>
      <div class="product-body">
        <div class="product-brand"><?= htmlspecialchars($brand); ?></div>
        <div class="product-name"><?= htmlspecialchars($title); ?></div>
        <div class="product-rating"><span>★</span> <?= number_format($rating, 1); ?> <span style="color:var(--gray-400)">(<?= number_format($numRat); ?>)</span></div>
        <div class="product-footer">
          <div class="product-price"><?= $price; ?></div>
          <button class="product-compare-btn" onclick="event.stopPropagation();event.preventDefault();addToCompare({asin:'<?= htmlspecialchars($asin) ?>',source:'<?= htmlspecialchars($source) ?>',title:'<?= htmlspecialchars(str_replace("'","\\'",$p['title'] ?? '')) ?>',photo:'<?= htmlspecialchars(str_replace("'","\\'",$p['photo'] ?? '')) ?>',price:'<?= $p['price_usd'] > 0 ? number_format($p['price_usd'],2) : '' ?>',brand:'<?= htmlspecialchars(str_replace("'","\\'",$brand)) ?>',rating:'<?= $rating ?>',num_ratings:'<?= $numRat ?>',url:'<?= htmlspecialchars(str_replace("'","\\'",$p['affiliate_link'] ?? '')) ?>'})" title="Compare">⚖️</button>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- PAGINATION -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
    <a href="<?= $pageUrl; ?>&page=<?= $page - 1; ?>" class="page-num page-arrow">‹</a>
    <?php endif; ?>

    <?php
    $startPage = max(1, $page - 2);
    $endPage   = min($totalPages, $page + 2);
    // Show first page + dot before start
    if ($startPage > 1) {
        ?><a href="<?= $pageUrl; ?>&page=1" class="page-num">1</a><?php
        if ($startPage > 2) echo '<span class="page-num" style="border:none;background:transparent;min-width:auto;">…</span>';
    }
    for ($i = $startPage; $i <= $endPage; $i++):
    ?>
    <a href="<?= $pageUrl; ?>&page=<?= $i; ?>" class="page-num <?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor;
    // Show dot + last page after end
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) echo '<span class="page-num" style="border:none;background:transparent;min-width:auto;">…</span>';
        ?><a href="<?= $pageUrl; ?>&page=<?= $totalPages; ?>" class="page-num"><?= $totalPages; ?></a><?php
    } ?>

    <?php if ($page < $totalPages): ?>
    <a href="<?= $pageUrl; ?>&page=<?= $page + 1; ?>" class="page-num page-arrow">›</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php else: ?>
  <div class="no-results">
    <div>🔍</div>
    <?php if ($is_generic_search): ?>
    <p>"<?= htmlspecialchars($search_q); ?>" ke liye koi products nahi mile.</p>
    <p style="font-size:13px;color:var(--gray-400);margin-top:6px;">Koi aur keyword try karein ya marketplace se search karein.</p>
    <?php else: ?>
    <p>Is category mein abhi koi products nahi hain.</p>
    <p style="font-size:13px;color:var(--gray-400);margin-top:6px;">Koi aur keyword search karein ya category change karein.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  </div>
  </div>
</div>
<script src="js/category.js?v=<?= filemtime(__DIR__.'/js/category.js') ?>"></script>
<script src="js/compare.js?v=<?= filemtime(__DIR__.'/js/compare.js') ?>"></script>
<script>
// ── htmlEscape (local copy, since common.js is not loaded on this page) ──
function htmlEscape(str) {
  var d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML;
}

// ── Search suggestions for filter bar on category.php ──
(function(){
  var input = document.getElementById('fbSearchInput');
  if(!input) return;
  var dd = document.getElementById('fbSearchSuggestions');
  if(!dd) return;

  var _timer = null;

  function doFetch(val){
    if(val.length < 2){ dd.classList.remove('active'); dd.innerHTML=''; return; }
    fetch('api/suggestions.php?q='+encodeURIComponent(val))
      .then(function(r){ return r.json(); })
      .then(function(data){
        if(!data.suggestions || data.suggestions.length===0){
          dd.classList.remove('active'); dd.innerHTML=''; return;
        }
        var html='';
        data.suggestions.forEach(function(s){
          var safe = s.replace(/'/g,"\\'");
          html += '<div class="suggestion-item" onclick="fbSelectSuggestion(\''+safe+'\')">'
            + '<span class="suggestion-icon">🔍</span>'
            + '<span class="suggestion-text">'+htmlEscape(s)+'</span>'
            + '</div>';
        });
        dd.innerHTML=html;
        dd.classList.add('active');
      })
      .catch(function(){ dd.classList.remove('active'); dd.innerHTML=''; });
  }

  input.addEventListener('input',function(){
    clearTimeout(_timer);
    _timer=setTimeout(function(){ doFetch(input.value.trim()); },300);
  });

  input.addEventListener('focus',function(){
    if(input.value.trim().length>=2) doFetch(input.value.trim());
  });

  input.addEventListener('blur',function(){
    setTimeout(function(){ dd.classList.remove('active'); dd.innerHTML=''; },200);
  });
})();

window.fbSelectSuggestion = function(text){
  var inp = document.getElementById('fbSearchInput');
  var dd = document.getElementById('fbSearchSuggestions');
  if(inp) {
    inp.value=text;
    inp.closest('form').submit();
  }
  if(dd){ dd.classList.remove('active'); dd.innerHTML=''; }
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
