<?php
require __DIR__ . "/db.php";

$sql = "
  SELECT
    p.place_id,
    p.name AS place_name,
    c.name AS city_name
  FROM Place_of_Interest p
  JOIN City c ON c.city_id = p.city_id
  ORDER BY p.place_id DESC
  LIMIT 50
";
$rows = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>RSS Feed Viewer</title>
  <style>
    /* Basic styling for the back button */
    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      padding: 8px 14px;
      background-color: #f0f0f0;
      border: 1px solid #ccc;
      border-radius: 8px;
      text-decoration: none;
      color: #333;
      font-family: sans-serif;
      font-weight: 600;
      cursor: pointer;
    }
    .back-btn:hover {
      background-color: #e0e0e0;
    }
  </style>
</head>
<body>

  <button class="back-btn" onclick="window.location.href='index.php'" aria-label="Return to the main index page">
    ← Back
  </button>

  <h1>Clickable Feed Viewer</h1>
  <p>This is just an HTML viewer. The real RSS feed is <a href="rss.php">rss.php</a>.</p>

  <ul>
    <?php foreach ($rows as $r): ?>
      <li>
        <a href="place.php?place_id=<?= (int)$r["place_id"] ?>">
          <?= htmlspecialchars($r["place_name"]) ?> - <?= htmlspecialchars($r["city_name"]) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
