<?php
/* photo_widget.php */

// 1. Load the configuration and database connection
$config = require 'config.php';

// 2. Settings
$api_key  = $config['api']['pixabay_key'];
$per_page = 3; 

// 3. Mapping: ID => Name
$cities_map = [
    1 => 'Liverpool',
    2 => 'Cologne'
];

// --- NEW FILTER LOGIC ---
// Check if ?city=cologne or ?city=liverpool is in the URL
$filter_city = isset($_GET['city']) ? strtolower(trim($_GET['city'])) : null;

$cities = [];
foreach ($cities_map as $id => $name) {
    // If no filter is set, show all. If filter is set, only add the match.
    if (!$filter_city || strtolower($name) === $filter_city) {
        $cities[$id] = $name;
    }
}

// --- HANDLE USER UPLOADS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_photo'])) {
    $city_id = $_POST['city_id'];
    $ext = strtolower(pathinfo($_FILES['new_photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png'])) {
        if (!file_exists("user_pics/")) mkdir("user_pics/", 0777, true);
        $target = "user_pics/{$city_id}_" . time() . ".{$ext}";
        if (move_uploaded_file($_FILES['new_photo']['tmp_name'], $target)) {
            header("Location: ".$_SERVER['PHP_SELF'].(empty($_SERVER['QUERY_STRING']) ? "" : "?".$_SERVER['QUERY_STRING']));
            exit;
        }
    }
}

// --- FETCH PHOTOS ---
$display_data = [];
foreach ($cities as $db_id => $city_name) {
    $id = $db_id;
    $city_key = strtolower($city_name);
    
    $page = isset($_GET["{$city_key}_p"]) ? (int)$_GET["{$city_key}_p"] : 1;
    if ($page < 1) $page = 1;

    $all_local = file_exists("user_pics/") ? glob("user_pics/{$id}_*") : [];
    $display_user = array_slice($all_local, ($page-1)*$per_page, $per_page);

    $slots = $per_page - count($display_user);
    $display_api = [];

    if ($slots > 0) {
        $stmt = $pdo->prepare("SELECT image_url, caption FROM Photos WHERE city_id=? AND page_num=? ORDER BY photo_id ASC LIMIT ?");
        $stmt->bindValue(1, (string)$id, PDO::PARAM_STR);
        $stmt->bindValue(2, $page, PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$slots, PDO::PARAM_INT);
        $stmt->execute();
        $display_api = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($display_api) < $slots) {
            $url = "https://pixabay.com/api/?key={$api_key}&q=" . urlencode($city_name) . "&page={$page}&per_page={$per_page}";
            
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $api_json = @file_get_contents($url, false, $ctx);
            $api = $api_json ? json_decode($api_json, true) : ['hits' => []];
            
            foreach ($api['hits'] as $photo) {
                $stmtIns = $pdo->prepare("INSERT INTO Photos (city_id, page_num, image_url, caption) VALUES (?, ?, ?, ?)");
                $stmtIns->execute([(string)$id, $page, $photo['webformatURL'], $photo['pageURL']]);
                $display_api[] = ['image_url' => $photo['webformatURL'], 'caption' => $photo['pageURL']];
                if (count($display_api) >= $slots) break;
            }
        }
    }

    $display_data[$id] = [
        'name'  => $city_name,
        'key'   => $city_key,
        'page'  => $page,
        'user'  => $display_user,
        'api'   => $display_api
    ];
}
?>

<link rel="stylesheet" href="photo_widget.css">

<?php if (empty($display_data)): ?>
    <p>No city selected or city not found.</p>
<?php endif; ?>

<?php foreach ($display_data as $id => $data): 
    $city_key = $data['key'];
    $page     = $data['page'];

    $params = $_GET;
    $params["{$city_key}_p"] = $page - 1;
    $prev = "?" . http_build_query($params);
    $prev_class = ($page <= 1) ? "hidden" : "";
    
    $params["{$city_key}_p"] = $page + 1;
    $next = "?" . http_build_query($params);
?>
<div class="city-card">
    <div class="nav">
        <a href="<?= htmlspecialchars($prev) ?>" class="btn <?= $prev_class ?>">Prev</a>
        <div class="city-header">
            <h2 class="city-title">
                <?= htmlspecialchars($data['name']) ?>
                <form action="" method="POST" enctype="multipart/form-data" style="display:inline;">
                    <label class="add-btn">+ ADD<input type="file" name="new_photo" onchange="this.form.submit()"></label>
                    <input type="hidden" name="city_id" value="<?= $id ?>">
                </form>
            </h2>
            <span class="page-counter">Page <?= $page ?></span>
        </div>
        <a href="<?= htmlspecialchars($next) ?>" class="btn">Next</a>
    </div>
    
    <div class="grid">
        <?php foreach ($data['user'] as $lp): ?>
            <div style="position:relative;">
                <span class="user-badge">User</span>
                <img src="<?= htmlspecialchars($lp) ?>" alt="User upload" loading="lazy">
            </div>
        <?php endforeach; ?>
        
        <?php foreach ($data['api'] as $img): ?>
            <a href="<?= htmlspecialchars($img['caption']) ?>" target="_blank" style="position:relative; display:block;">
                <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="Pixabay image" loading="lazy">
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>