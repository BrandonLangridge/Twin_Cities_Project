<?php
/* maps.php */

// Link to config.php
// This provides the $pdo connection and ensures consistent DB settings.
$config = require_once __DIR__ . '/config.php'; 
require_once __DIR__ . '/comments_logic.php'; 

// Check if weather_widget.php exists to prevent a fatal "file not found" error
if (file_exists(__DIR__ . '/weather_widget.php')) {
    require_once __DIR__ . '/weather_widget.php';
}

// CITY ROUTING & DATA FETCHING
$cities = [
    "Liverpool" => ["lat" => 53.4106, "lon" => -2.9779],
    "Cologne"   => ["lat" => 50.9333, "lon" => 6.95]
];

$currentCityName = isset($_GET['city']) && array_key_exists(ucfirst($_GET['city']), $cities) ? ucfirst($_GET['city']) : "Liverpool";
$coords = $cities[$currentCityName];

// DATABASE SYNC
$currentCityId = 1; 
try {
    $stmtId = $pdo->prepare("SELECT city_id FROM Cities WHERE name = ? LIMIT 1");
    $stmtId->execute([$currentCityName]);
    $cityDbRow = $stmtId->fetch();
    if ($cityDbRow) {
        $currentCityId = $cityDbRow['city_id'];
    }
} catch (Exception $e) {
    
}

// CONFIGURATION PARAMETERS
$weatherBase = rtrim($config['api']['weather_base_url'], '/');
$units = $config['api']['weather_units'] ?? 'metric';

// Define weather codes
$weatherCodes = [
    0 => "Clear Sky", 1 => "Mainly Clear", 2 => "Partly Cloudy", 3 => "Overcast",
    45 => "Fog", 51 => "Drizzle", 61 => "Rain", 71 => "Snow", 95 => "Thunderstorm"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($currentCityName); ?> | Map & Forecast</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="weather_style.css">
</head>

<body class="map-page">

  <div class="page-header">
    <button id="back-btn" class="toggle-button" onclick="window.location.href='index.php'">← Back</button>
    <h1><?= htmlspecialchars($currentCityName); ?> Map & Forecast</h1>
    
    <select id="colorModeSelect" class="toggle-button">
      <option value="none">Color-Blind Mode: OFF</option>
      <option value="protan">Protanopia</option>
      <option value="deutan">Deuteranopia</option>
      <option value="tritan">Tritanopia</option>
    </select>
  </div>

  <div id="map-wrapper">
    <div id="map"></div>
  </div>

  <div class="weather-dashboard">
    <?php 
      
      if (function_exists('renderWeatherWidget')) {
          renderWeatherWidget(
              $currentCityName, 
              $coords['lat'], 
              $coords['lon'], 
              $weatherBase, 
              $units, 
              $weatherCodes
          );
      } 
    ?>
  </div>

  <section class="comments-section">
      <div class="container">
          <h3>Community Comments</h3>
          
          <form action="comments_logic.php" method="POST" class="comment-form">
              <input type="hidden" name="city_id" value="<?= (int)$currentCityId; ?>">
              <input type="text" name="user_name" placeholder="Your Name" required>
              <textarea name="comment_text" rows="4" placeholder="Share your thoughts..." required></textarea>
              <button type="submit" name="submit_comment" class="toggle-button">Post Comment</button>
          </form>

          <div class="comments-list">
              <?php
              $comments = getCommentsForCity($currentCityId, $pdo);
              if ($comments):
                  foreach ($comments as $c): ?>
                      <div class="comment-card">
                          <div class="comment-header">
                              <div>
                                  <strong><?= htmlspecialchars($c['user_name']); ?></strong>
                                  <small><?= date("j M Y", strtotime($c['created_at'])); ?></small>
                              </div>
                              <form action="comments_logic.php" method="POST" style="display:inline;">
                                  <input type="hidden" name="delete_id" value="<?= $c['comments_id']; ?>">
                                  <input type="hidden" name="city_id" value="<?= $currentCityId; ?>">
                                  <button type="submit" name="delete_comment" class="delete-btn">&times;</button>
                              </form>
                          </div>
                          <p><?= htmlspecialchars($c['comment_text']); ?></p>
                      </div>
                  <?php endforeach;
              endif; ?>
          </div>
      </div>
  </section>

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="pois.js"></script> 
  <script src="maps.js"></script> 
  <script>
    // Accessibility persistence
    const colorSelect = document.getElementById("colorModeSelect");
    function applyColorMode(mode) {
      document.documentElement.className = "";
      if (mode !== "none") document.documentElement.classList.add(mode);
    }
    colorSelect.addEventListener("change", (e) => {
      applyColorMode(e.target.value);
      localStorage.setItem("colorMode", e.target.value);
    });
    const saved = localStorage.getItem("colorMode") || "none";
    applyColorMode(saved);
    colorSelect.value = saved;
  </script>
</body>
</html>









