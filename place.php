<?php
// Author: Gemini Refactor - Modern Dashboard Style
require __DIR__ . "/db.php";

if (!isset($_GET["place_id"])) {
    echo "<div style='padding:20px; font-family:sans-serif;'>No place selected.</div>";
    exit;
}

$placeId = (int) $_GET["place_id"];

$sql = "
  SELECT
    p.name AS place_name,
    p.type AS place_type,
    p.capacity,
    p.latitude,
    p.longitude,
    p.description,
    c.name AS city_name,
    c.country
  FROM Place_of_Interest p
  JOIN City c ON c.city_id = p.city_id
  WHERE p.place_id = :id
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(":id", $placeId, PDO::PARAM_INT);
$stmt->execute();
$place = $stmt->fetch();

if (!$place) {
    echo "<div style='padding:20px; font-family:sans-serif;'>Place not found.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($place["place_name"]) ?> | Details</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Adding specific layout tweaks to match the details.html structure */
    .container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: sans-serif; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .content { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
    .details h1 { margin-top: 0; }
    .meta { color: #666; font-size: 0.9rem; margin-bottom: 15px; }
    .specs { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .spec-item b { display: block; font-size: 0.75rem; text-transform: uppercase; color: #999; }
    
    /* Accessibility Class from your details.html */
    .colorblind { filter: saturate(0.5) contrast(1.2); }
  </style>
</head>

<body>
  <div class="container" role="main">
    <div class="header">
      <a href="rss_view.php" aria-label="Go back to feed">← Back to Feed</a>
      <button id="accessibility-toggle" aria-pressed="false" aria-label="Toggle colour blind mode">
        Colour Blind Mode
      </button>
    </div>

    <div id="output" aria-live="polite">
      <div class="content">
        <div class="details">
          <div class="meta">
            <?= htmlspecialchars($place["place_type"]) ?> &bull; 
            <?= htmlspecialchars($place["city_name"]) ?>, <?= htmlspecialchars($place["country"]) ?>
          </div>
          
          <h1><?= htmlspecialchars($place["place_name"]) ?></h1>
          
          <p><?= nl2br(htmlspecialchars($place["description"])) ?></p>
          
          <div class="specs">
            <div class="spec-item">
              <b>Capacity</b>
              <?= number_format($place["capacity"]) ?> People
            </div>
            <div class="spec-item">
              <b>Coordinates</b>
              <?= htmlspecialchars($place["latitude"]) ?>, <?= htmlspecialchars($place["longitude"]) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    /* ACCESSIBILITY LOGIC 
       Retained from your details.html to keep the experience consistent.
    */
    let colorBlindEnabled = localStorage.getItem("colorBlindEnabled") === "true";
    if (colorBlindEnabled) { document.body.classList.add("colorblind"); }

    const toggleBtn = document.getElementById("accessibility-toggle");
    toggleBtn.setAttribute("aria-pressed", colorBlindEnabled);

    toggleBtn.addEventListener("click", () => {
      const enabled = document.body.classList.toggle("colorblind");
      localStorage.setItem("colorBlindEnabled", enabled);
      toggleBtn.setAttribute("aria-pressed", enabled);
    });
  </script>
</body>
</html>
