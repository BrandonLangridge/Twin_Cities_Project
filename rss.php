<?php
// 1. Database connection (db.php already requires config.php)
require __DIR__ . "/db.php";

// Access the $config variable that was defined inside db.php
// We need to make sure $config is available here.
if (!isset($config)) {
    $config = require __DIR__ . '/config.php';
}

header("Content-Type: application/rss+xml; charset=UTF-8");

function esc($s) {
    return htmlspecialchars($s ?? "", ENT_QUOTES | ENT_XML1, "UTF-8");
}

$max = (int)$config["rss"]["max_items"];
$baseUrl = rtrim($config["rss"]["base_url"], "/");

// 2. Fetch News
$newsSql = "
  SELECT n.title, n.content, n.date_posted, c.name AS city_name 
  FROM News n 
  JOIN City c ON c.city_id = n.city_id 
  ORDER BY n.date_posted DESC 
  LIMIT :max";
$newsStmt = $pdo->prepare($newsSql);
$newsStmt->bindValue(":max", $max, PDO::PARAM_INT);
$newsStmt->execute();
$newsRows = $newsStmt->fetchAll();

// 3. Fetch Places (Matched to your actual DB columns: 'type' and 'description')
$poiSql = "
  SELECT p.place_id, p.name AS place_name, p.type, p.description, 
         c.name AS city_name, c.country AS city_country 
  FROM Place_of_Interest p 
  JOIN City c ON c.city_id = p.city_id 
  ORDER BY p.place_id DESC 
  LIMIT :max";
$poiStmt = $pdo->prepare($poiSql);
$poiStmt->bindValue(":max", $max, PDO::PARAM_INT);
$poiStmt->execute();
$poiRows = $poiStmt->fetchAll();

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<rss version="2.0">
  <channel>
    <title><?= esc($config["rss"]["title"]) ?></title>
    <link><?= esc($baseUrl) ?></link>
    <description><?= esc($config["rss"]["description"]) ?></description>
    <language>en-gb</language>
    <lastBuildDate><?= esc(date(DATE_RSS)) ?></lastBuildDate>

    <?php foreach ($newsRows as $n): ?>
      <item>
        <title><?= esc("[NEWS] " . $n["title"] . " - " . $n["city_name"]) ?></title>
        <pubDate><?= esc(date(DATE_RSS, strtotime($n["date_posted"]))) ?></pubDate>
        <description><![CDATA[<?= $n["content"] ?>]]></description>
        <category>News</category>
      </item>
    <?php endforeach; ?>

    <?php foreach ($poiRows as $r): ?>
      <item>
        <title><?= esc("[POI] " . $r["place_name"] . " - " . $r["city_name"]) ?></title>
        <link><?= esc($baseUrl . "/place.php?place_id=" . $r["place_id"]) ?></link>
        <guid isPermaLink="false"><?= esc("place-" . $r["place_id"]) ?></guid>
        <pubDate><?= esc(date(DATE_RSS)) ?></pubDate>
        <description><![CDATA[
            <strong>City:</strong> <?= esc($r["city_name"]) ?>, <?= esc($r["city_country"]) ?><br/>
            <strong>Type:</strong> <?= esc($r["type"]) ?><br/>
            <strong>Description:</strong> <?= esc($r["description"]) ?>
        ]]></description>
      </item>
    <?php endforeach; ?>
  </channel>
</rss>
