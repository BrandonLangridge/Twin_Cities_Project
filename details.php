<?php
/* details.php */

// Grab the POI name from the URL to set the page title
$poiTitle = isset($_GET['poi']) ? htmlspecialchars(str_replace('_', ' ', $_GET['poi'])) : "POI Details";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $poiTitle ?> | Twin Cities</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body class="details-page">
  <div class="container" role="main">
    <div class="page-header header">
      <a href="javascript:history.back()" id="back-btn" class="toggle-button" aria-label="Go back to map">← Back</a>
      
      <h1>Point of Interest</h1>

      <select id="colorModeSelect" class="toggle-button color-select-dropdown">
        <option value="none">Color-Blind Mode: OFF</option>
        <option value="protan">Protanopia</option>
        <option value="deutan">Deuteranopia</option>
        <option value="tritan">Tritanopia</option>
      </select>
    </div>

    <div id="output" aria-live="polite">
        <div class="loading-state" style="padding: 40px; text-align: center; color: #666;">
            Loading details...
        </div>
    </div>
  </div>

  <svg style="display:none" version="1.1" xmlns="http://www.w3.org/2000/svg">
    <defs>
      <filter id="protanopia"><feColorMatrix type="matrix" values="0.567, 0.433, 0, 0, 0, 0.558, 0.442, 0, 0, 0, 0, 0.242, 0.758, 0, 0, 0, 0, 0, 1, 0" /></filter>
      <filter id="deuteranopia"><feColorMatrix type="matrix" values="0.625, 0.375, 0, 0, 0, 0.7, 0.3, 0, 0, 0, 0, 0.3, 0.7, 0, 0, 0, 0, 0, 1, 0" /></filter>
      <filter id="tritanopia"><feColorMatrix type="matrix" values="0.95, 0.05, 0, 0, 0, 0, 0.433, 0.567, 0, 0, 0, 0.475, 0.525, 0, 0, 0, 0, 0, 1, 0" /></filter>
    </defs>
  </svg>

  <script>
    const voiceEnabled = localStorage.getItem("voiceEnabled") === "true";
    const colorSelect = document.getElementById("colorModeSelect");

    function applyColorMode(mode) {
      document.documentElement.classList.remove("protan", "deutan", "tritan");
      if (mode !== "none") document.documentElement.classList.add(mode);
    }

    function speak(text) {
      if (!voiceEnabled || !window.speechSynthesis) return;
      window.speechSynthesis.cancel();
      const msg = new SpeechSynthesisUtterance(text);
      window.speechSynthesis.speak(msg);
    }

    const savedMode = localStorage.getItem("colorMode") || "none";
    applyColorMode(savedMode);
    colorSelect.value = savedMode;

    colorSelect.addEventListener("change", (e) => {
      const mode = e.target.value;
      applyColorMode(mode);
      localStorage.setItem("colorMode", mode);
      speak(mode !== "none" ? `${mode} mode enabled` : "Color blind mode disabled");
    });

    /* DATA FETCHING LOGIC */
    const params = new URLSearchParams(window.location.search);
    const poiName = params.get("poi"); 
    const output = document.getElementById("output");

    if (!poiName) {
      output.innerHTML = "<div class='error' style='padding:40px; text-align:center;'>No POI specified.</div>";
    } else {
      loadPOI(poiName);
    }

    async function loadPOI(name) {
      try {
        const response = await fetch(`https://en.wikipedia.org/api/rest_v1/page/summary/${encodeURIComponent(name)}`);
        const data = await response.json();
        
        if (!response.ok || !data.title) throw new Error();

        output.innerHTML = `
          <div class="content" style="background:#fff; padding:20px; border-radius:8px; border:1px solid #ddd;">
            <div class="image" style="margin-bottom:20px;">
              ${data.thumbnail ? `<img src="${data.thumbnail.source}" alt="${data.title}" style="max-width:100%; border-radius:4px;">` : ""}
            </div>
            <div class="details">
              <h1 style="margin-top:0;">${data.title}</h1>
              <p style="line-height:1.6;">${data.extract}</p>
              <div class="source" style="margin-top:15px; font-size:0.8em; color:#666;">
                Source: <a href="${data.content_urls.desktop.page}" target="_blank">Wikipedia</a>
              </div>
            </div>
          </div>
        `;
        
        speak(`Showing details for ${data.title}`);

      } catch (err) {
        output.innerHTML = "<div class='error' style='padding:40px; text-align:center;'>Unable to load details for \"" + name.replace(/_/g, ' ') + "\". Please check your connection or POI name.</div>";
        speak("Unable to load details.");
      }
    }
  </script>
</body>
</html>






  

