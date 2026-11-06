<?php

function get_category_id($connect, $category_name, $create = true) {
    $category_name = trim($category_name);
    if ($category_name == '') return null;

    $normalized = strtolower($category_name);
    $normalized = str_replace(["'", "-", "_"], " ", $normalized);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    $normalized = trim($normalized);

    $searchSafe = mysqli_real_escape_string($connect, $normalized);

    // ── STEP 1: Exact match via SQL (fast, no PHP loop) ──
    // Normalize on DB side: lowercase + replace ' - _ with space + collapse double spaces
    $sql = "SELECT id, category_name FROM categories 
            WHERE REPLACE(REPLACE(REPLACE(REPLACE(LOWER(category_name), \"'\", ' '), '-', ' '), '_', ' '), '  ', ' ') = '$searchSafe'
            LIMIT 1";
    $result = mysqli_query($connect, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int)$row['id'];
    }

    // ── STEP 2: No exact match — pre-filter categories sharing at least one word ──
    $words = array_unique(array_filter(explode(' ', $normalized)));
    $likeClauses = [];
    foreach ($words as $word) {
        $w = mysqli_real_escape_string($connect, $word);
        $likeClauses[] = "category_name LIKE '%$w%'";
    }

    if (!empty($likeClauses)) {
        $where = implode(' OR ', $likeClauses);
        $result = mysqli_query($connect, "SELECT id, category_name FROM categories WHERE ($where)");
    } else {
        $result = mysqli_query($connect, "SELECT id, category_name FROM categories");
    }

    // ── STEP 3: Loop through pre-filtered rows (much smaller set) ──
    while ($row = mysqli_fetch_assoc($result)) {
        $existing = strtolower($row['category_name']);
        $existing = str_replace(["'", "-", "_"], " ", $existing);
        $existing = preg_replace('/\s+/', ' ', $existing);
        $existing = trim($existing);

        if ($existing === $normalized) return (int)$row['id'];

        // levenshtein() is significantly faster than similar_text() (~10-50x)
        // For short strings (category names < 100 chars), it's extremely efficient
        $existingFlat = str_replace(' ', '', $existing);
        $normalizedFlat = str_replace(' ', '', $normalized);
        $dist = levenshtein($existingFlat, $normalizedFlat);

        if ($dist === -1) {
            // Fallback for strings > 255 chars (extremely rare for category names)
            similar_text($existingFlat, $normalizedFlat, $percent);
            if ($percent >= 90) return (int)$row['id'];
        } else {
            $maxLen = max(strlen($existingFlat), strlen($normalizedFlat));
            if ($maxLen > 0) {
                $percent = (1 - $dist / $maxLen) * 100;
                if ($percent >= 90) return (int)$row['id'];
            }
        }
    }

    // ── STEP 4: No match found — optionally create ──
    if ($create) {
        $name_safe = mysqli_real_escape_string($connect, $category_name);
        mysqli_query($connect, "INSERT INTO categories (category_name, total_products) VALUES ('$name_safe', 0)");
        return mysqli_insert_id($connect);
    }

    return null;
}


function update_category_count($connect, $category_id) {
    if (!$category_id) return;

    $all_tables = array(
        'products',
        'aliexpress_products',
        'ebay_products',
        'walmart_products'
    );

    $parts = array();
    foreach ($all_tables as $table) {
        $t     = mysqli_real_escape_string($connect, $table);
        $check = mysqli_query($connect, "SHOW TABLES LIKE '$t'");
        if ($check && mysqli_num_rows($check) > 0) {
            $parts[] = "(SELECT COUNT(*) FROM `$t` WHERE category_id = $category_id)";
        }
    }

    if (empty($parts)) return;

    mysqli_query($connect, "
        UPDATE categories
        SET total_products = (" . implode(' + ', $parts) . ")
        WHERE id = $category_id
    ");
}


function generate_keyword_variations($term) {
    $term       = trim(strtolower($term));
    $variations = array($term);

    $category_aliases = array(

        "men's shoes"    => array("mens shoes", "men shoes", "mens shoe", "men shoe"),
        "mens shoes"     => array("men's shoes", "men shoes", "mens shoe", "men shoe"),
        "men shoes"      => array("mens shoes", "men's shoes", "mens shoe", "men shoe"),
        "womens shoes"   => array("women's shoes", "women shoes", "ladies shoes", "womens shoe"),
        "women shoes"    => array("womens shoes", "women's shoes", "ladies shoes"),
        "kids shoes"     => array("kid shoes", "children shoes", "children's shoes", "boys shoes", "girls shoes"),
        "sports shoes"   => array("sport shoes", "running shoes", "athletic shoes", "gym shoes", "sneakers"),

        "men's clothing" => array("mens clothing", "men clothing", "mens clothes", "men clothes", "mens fashion"),
        "mens clothing"  => array("men's clothing", "men clothing", "mens clothes", "men clothes"),
        "women's clothing"=> array("womens clothing", "women clothing", "ladies clothing", "womens clothes", "women fashion"),
        "womens clothing" => array("women's clothing", "women clothing", "ladies clothing", "womens clothes"),
        "kids clothing"  => array("kid clothing", "children clothing", "children's clothing", "boys clothing", "girls clothing"),
        "winter wear"    => array("winter clothes", "winter clothing", "warm clothes", "cold weather clothes", "jackets"),

        "mobile phones"  => array("mobile phone", "smartphones", "smartphone", "cell phones", "cell phone", "mobiles"),
        "mobile phone"   => array("mobile phones", "smartphones", "smartphone", "cell phone"),
        "smartphones"    => array("smartphone", "mobile phones", "mobile phone", "cell phones"),
        "smartphone"     => array("smartphones", "mobile phone", "mobile phones"),
        "laptops"        => array("laptop", "notebook", "notebooks", "laptop computer"),
        "laptop"         => array("laptops", "notebook", "laptop computer"),
        "tablets"        => array("tablet", "ipad", "android tablet", "tab"),
        "smart watches"  => array("smart watch", "smartwatch", "smartwatches", "fitness watch"),
        "smartwatch"     => array("smartwatches", "smart watch", "smart watches"),

        "monitors"       => array("monitor", "computer monitor", "display screen", "pc monitor"),
        "keyboards"      => array("keyboard", "computer keyboard", "wireless keyboard", "mechanical keyboard"),
        "mouse"          => array("computer mouse", "wireless mouse", "gaming mouse", "mice"),
        "printers"       => array("printer", "inkjet printer", "laser printer", "office printer"),

        "gaming consoles"    => array("gaming console", "game console", "game consoles", "playstation", "xbox", "nintendo"),
        "gaming accessories" => array("gaming accessory", "game accessories", "gaming gear", "gaming peripherals"),
        "pc gaming"          => array("computer gaming", "gaming pc", "gaming computer", "desktop gaming"),
        "vr headsets"        => array("vr headset", "virtual reality headset", "vr glasses", "virtual reality"),

        "kitchen appliances" => array("kitchen appliance", "home appliances", "cooking appliances", "kitchen items", "kitchen gadgets"),
        "cookware"           => array("cooking pots", "pots and pans", "cooking utensils", "kitchen cookware", "cooking pan"),
        "furniture"          => array("home furniture", "office furniture", "chairs", "tables", "sofa"),
        "home decor"         => array("home decoration", "home decorations", "room decor", "house decor", "decorative items"),

        "skincare"       => array("skin care", "face care", "facial care", "moisturizer", "face cream"),
        "skin care"      => array("skincare", "face care", "facial products", "moisturizer"),
        "hair care"      => array("haircare", "hair products", "shampoo", "conditioner", "hair treatment"),
        "haircare"       => array("hair care", "hair products", "shampoo"),
        "makeup"         => array("cosmetics", "beauty makeup", "face makeup", "beauty products"),
        "grooming"       => array("men grooming", "mens grooming", "personal grooming", "shaving", "beard care"),

        "gym equipment"      => array("gym gear", "fitness equipment", "workout equipment", "exercise equipment", "home gym"),
        "supplements"        => array("protein supplements", "health supplements", "vitamins", "protein powder", "nutrition"),
        "yoga accessories"   => array("yoga accessory", "yoga mat", "yoga gear", "yoga equipment", "yoga items"),
        "health devices"     => array("health device", "medical devices", "health gadgets", "fitness tracker", "blood pressure monitor"),

        "cricket"        => array("cricket bat", "cricket equipment", "cricket gear", "cricket kit", "cricket ball"),
        "football"       => array("football gear", "soccer", "football equipment", "soccer ball", "football kit"),
        "camping"        => array("camping gear", "outdoor camping", "camping equipment", "hiking", "tent"),
        "cycling"        => array("bicycle", "bike", "cycling gear", "cycle", "cycling equipment"),

        "men's watches"  => array("mens watches", "men watches", "mens watch", "men watch", "gents watch"),
        "women's watches"=> array("womens watches", "women watches", "ladies watches", "ladies watch"),
        "luxury watches" => array("luxury watch", "premium watches", "designer watches", "branded watches"),

        "backpacks"      => array("backpack", "school bag", "laptop bag", "rucksack", "travel backpack"),
        "handbags"       => array("handbag", "purse", "ladies bag", "shoulder bag", "women bag", "women bags"),
        "travel bags"    => array("travel bag", "luggage bag", "trolley bag", "suitcase", "luggage"),
        "wallets"        => array("wallet", "mens wallet", "leather wallet", "card holder", "purse"),

        "rings"          => array("ring", "finger ring", "gold ring", "silver ring", "diamond ring"),
        "necklaces"      => array("necklace", "chain", "pendant", "neck chain", "gold necklace"),
        "bracelets"      => array("bracelet", "bangle", "bangles", "wrist band", "wristband"),
        "earrings"       => array("earring", "ear rings", "ear studs", "studs", "hoops"),

        "educational toys"      => array("educational toy", "learning toys", "kids learning toys", "educational games"),
        "action figures"        => array("action figure", "toy figures", "superhero toys", "character toys"),
        "board games"           => array("board game", "family games", "table games", "card games"),
        "remote control toys"   => array("remote control toy", "rc toys", "rc car", "remote car", "rc helicopter"),

        "baby clothing"  => array("baby clothes", "infant clothes", "newborn clothes", "baby dress", "toddler clothes"),
        "baby care"      => array("baby skincare", "baby products", "infant care", "baby essentials", "baby hygiene"),
        "baby toys"      => array("baby toy", "infant toys", "newborn toys", "toddler toys", "kids toys"),
        "strollers"      => array("stroller", "baby stroller", "pram", "baby pram", "pushchair"),

        "car accessories"        => array("car accessory", "auto accessories", "vehicle accessories", "car parts", "car items"),
        "car electronics"        => array("car electronic", "auto electronics", "car gadgets", "car audio", "dash cam"),
        "motorcycle accessories" => array("motorcycle accessory", "bike accessories", "motorbike accessories", "biker gear"),
        "car care"               => array("car cleaning", "auto care", "car wash", "car maintenance", "car polish"),

        "power tools"      => array("power tool", "electric tools", "cordless tools", "drill machine", "tool set"),
        "hand tools"       => array("hand tool", "manual tools", "screwdriver", "wrench", "pliers"),
        "safety equipment" => array("safety gear", "protective equipment", "safety items", "work safety", "ppe"),
        "industrial tools" => array("industrial tool", "heavy tools", "workshop tools", "professional tools"),

        "indoor lighting"    => array("indoor lights", "home lighting", "room lights", "ceiling light", "table lamp"),
        "outdoor lighting"   => array("outdoor lights", "garden lights", "exterior lighting", "street lights", "garden lamp"),
        "led lights"         => array("led light", "led bulbs", "led bulb", "energy saving lights", "led strips"),
        "decorative lights"  => array("decorative light", "fairy lights", "string lights", "mood lights", "party lights"),

        "phone cases"    => array("phone case", "mobile cover", "mobile case", "back cover", "phone cover"),
        "chargers"       => array("charger", "mobile charger", "phone charger", "usb charger", "fast charger"),
        "power banks"    => array("power bank", "portable charger", "external battery", "powerbank"),
        "earbuds"        => array("earbud", "wireless earbuds", "bluetooth earbuds", "tws earbuds", "airpods"),

        "books"          => array("book", "novels", "novel", "textbooks", "textbook", "reading books"),
        "office supplies"  => array("office supply", "stationery", "office items", "desk supplies", "office stationery"),
        "school supplies"  => array("school supply", "school stationery", "student supplies", "school items"),
        "art supplies"     => array("art supply", "painting supplies", "drawing supplies", "craft supplies", "art materials"),

        "guitars"        => array("guitar", "acoustic guitar", "electric guitar", "bass guitar", "ukulele"),
        "piano"          => array("pianos", "keyboard piano", "digital piano", "electronic piano", "electric piano"),
        "drums"          => array("drum", "drum set", "drum kit", "percussion", "drum machine"),
        "studio equipment" => array("studio gear", "recording equipment", "music studio", "home studio", "recording studio"),
    );

    if (isset($category_aliases[$term])) {
        foreach ($category_aliases[$term] as $alias) {
            $variations[] = $alias;
        }
    }

    foreach ($variations as $v) {
        if (isset($category_aliases[$v])) {
            foreach ($category_aliases[$v] as $alias) {
                $variations[] = $alias;
            }
        }
    }

    if (preg_match('/^men\'?s? /', $term)) {
        $base = preg_replace('/^men\'?s? /', '', $term);
        $variations[] = "men $base";
        $variations[] = "mens $base";
        $variations[] = "men's $base";
    }
    if (preg_match('/^women\'?s? /', $term)) {
        $base = preg_replace('/^women\'?s? /', '', $term);
        $variations[] = "women $base";
        $variations[] = "womens $base";
        $variations[] = "women's $base";
        $variations[] = "ladies $base";
    }
    if (preg_match('/^kids? /', $term)) {
        $base = preg_replace('/^kids? /', '', $term);
        $variations[] = "kids $base";
        $variations[] = "kid $base";
        $variations[] = "children $base";
        $variations[] = "children's $base";
    }

    return array_slice(array_values(array_unique($variations)), 0, 4);
}
?>
