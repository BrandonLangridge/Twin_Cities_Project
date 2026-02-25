<?php
/* rss_view.php */

// Link to config.php
// This provides the $pdo connection and ensures consistent DB settings.
require_once "config.php"; 

$rows = []; 

try {
    /* The Query 
       Matches Cities/poi_id schema. */
    $sql = "
      SELECT
        p.poi_id,
        p.name AS place_name,
        c.name AS city_name
      FROM Place_of_Interest p
      JOIN Cities c ON c.city_id = p.city_id
      ORDER BY p.poi_id DESC
      LIMIT 50
    ";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // This will catch connection or query errors
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Feed Viewer</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .debug-box { background: #fee; border: 1px solid #f00; padding: 15px; margin: 20px; color: #900; }
  </style>
</head>
<body class="weather-dashboard">

  <div style="max-width: 800px; margin: 40px auto; background: white; padding: 30px; border-radius: 15px;">
    <button class="toggle-button" onclick="window.location.href='index.php'">← Back</button>
    
    <h1>Community Feed</h1>

    <?php if (isset($db_error)): ?>
        <div class="debug-box">
            <strong>Database Error:</strong> <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>

    <ul style="list-style: none; padding: 0;">
      <?php if (empty($rows) && !isset($db_error)): ?>
        <li style="padding: 20px; color: #666; text-align: center;">
            No items found in the database. 
            <br><small>Check if your 'Place_of_Interest' and 'Cities' tables have data.</small>
        </li>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <li style="padding: 15px; border-bottom: 1px solid #eee;">
            <a href="place.php?poi_id=<?= (int)$r["poi_id"] ?>" style="font-weight: bold; color: var(--accent);">
              <?= htmlspecialchars($r["place_name"]) ?>
            </a>
            <div style="font-size: 0.8rem; color: #666;">Location: <?= htmlspecialchars($r["city_name"]) ?></div>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</body>
</html>