<?php
/* rss.php */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Link to config.php
// This provides the $pdo connection and ensures consistent DB settings.
$config = require_once __DIR__ . "/config.php";

header("Content-Type: application/rss+xml; charset=UTF-8");

function esc($s) {
    return htmlspecialchars($s ?? "", ENT_QUOTES | ENT_XML1, "UTF-8");
}

$max = (int)($config["rss"]["max_items"] ?? 10);
$baseUrl = rtrim($config["rss"]["base_url"], "/");

try {
    $poiSql = "
      SELECT p.poi_id, p.name AS place_name, p.type, p.description, 
             c.name AS city_name, c.country AS city_country 
      FROM Place_of_Interest p 
      JOIN Cities c ON c.city_id = p.city_id 
      ORDER BY p.poi_id DESC 
      LIMIT :max";
    
    $poiStmt = $pdo->prepare($poiSql);
    $poiStmt->bindValue(":max", $max, PDO::PARAM_INT);
    $poiStmt->execute();
    $poiRows = $poiStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // RSS feeds should not output HTML errors, but for development we use die
    die("Database Error: " . $e->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<rss version="2.0">
  <channel>
    <title><?= esc($config["rss"]["title"]) ?></title>
    <link><?= esc($baseUrl) ?></link>
    <description><?= esc($config["rss"]["description"]) ?></description>
    <language>en-gb</language>
    <lastBuildDate><?= esc(date(DATE_RSS)) ?></lastBuildDate>

    <?php foreach ($poiRows as $r): ?>
      <item>
        <title><?= esc($r["place_name"] . " (" . $r["city_name"] . ")") ?></title>
        <link><?= esc($baseUrl . "/details.php?poi=" . urlencode(str_replace(' ', '_', $r["place_name"]))) ?></link>
        <description><?= esc($r["description"]) ?></description>
        <category><?= esc($r["type"]) ?></category>
        <guid isPermaLink="false">poi_<?= $r["poi_id"] ?></guid>
      </item>
    <?php endforeach; ?>
  </channel>
</rss>
