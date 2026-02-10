<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Twin Cities: Liverpool & Cologne | Mapping Project</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body class="index-body">

  <header>
    <h1>
      Twin Cities:
      <span class="h1-accent">Liverpool</span> &
      <span class="h1-accent">Cologne</span>
    </h1>
    <p>Click a city below to explore its map and points of interest</p>

    <div class="toggle-container">
      <button id="voiceToggle" class="toggle-button">Voice Feedback: OFF</button>
      <button id="colorToggle" class="toggle-button">Color-Blind Mode: OFF</button>
      <button id="weatherJump" class="toggle-button">View Weather Dashboard</button>
      
      <button id="rssJump" class="toggle-button" style="background: #ee802f;">View RSS Feed</button>
    </div>
  </header>

  <main>
    <div class="city-container">
      <a class="city" href="maps.html?city=liverpool">
        <img src="liverpool.jpg" alt="Liverpool city skyline">
        <h2>Liverpool, UK</h2>
        <span class="city-button">Explore Liverpool</span>
      </a>

      <a class="city" href="maps.html?city=cologne">
        <img src="cologne.jpg" alt="Cologne skyline with cathedral">
        <h2>Cologne, Germany</h2>
        <span class="city-button">Explore Cologne</span>
      </a>
    </div>
  </main>

  <div id="aria-live" aria-live="polite" style="position:absolute; left:-9999px;"></div>

  <script>
    /* Checks the browser's localStorage to see if the user previously turned on Voice or Color-Blind mode.
       This ensures the website "remembers" the user's settings.
    */
    let voiceEnabled = localStorage.getItem("voiceEnabled") === "true";
    let colorBlindEnabled = localStorage.getItem("colorBlindEnabled") === "true";

    // Grab document elements so we can interact with them via JavaScript.
    const voiceBtn = document.getElementById("voiceToggle");
    const colorBtn = document.getElementById("colorToggle");
    const weatherBtn = document.getElementById("weatherJump");
    const rssBtn = document.getElementById("rssJump");
    const ariaLive = document.getElementById("aria-live");

    // Apply the colorblind CSS class if the setting is true.
    if (colorBlindEnabled) {document.body.classList.add("colorblind");}

    // Update button labels to match the saved settings
    voiceBtn.textContent = `Voice Feedback: ${voiceEnabled ? "ON" : "OFF"}`;
    colorBtn.textContent = `Color-Blind Mode: ${colorBlindEnabled ? "ON" : "OFF"}`;

    /* NAVIGATION LOGIC (WEATHER & RSS)
       Handles moving to other pages. If voice is enabled, it speaks the action 
       and uses a 1-second timeout to give the speech engine time to start.
    */
    weatherBtn.addEventListener("click", () => {
      if (voiceEnabled) {
        speak("Navigating to the weather dashboard");
        setTimeout(() => { window.location.href = "weather_widget.php"; }, 1000);
      } else {
        window.location.href = "weather_widget.php";
      }
    });

    rssBtn.addEventListener("click", () => {
        if (voiceEnabled) {
            speak("Opening the city news feed viewer");
            // Opens the HTML-formatted RSS viewer in a new browser tab
            setTimeout(() => { window.open("rss_view.php", "_blank"); }, 1000);
        } else {
            window.open("rss_view.php", "_blank");
        }
    });

    /* ACCESSIBILITY TOGGLE HANDLERS
       When clicked, these update the variable, save it to the browser memory,
       and update what the user sees on the button.
    */
    voiceBtn.addEventListener("click", () => {
      voiceEnabled = !voiceEnabled;
      localStorage.setItem("voiceEnabled", voiceEnabled);
      voiceBtn.textContent = `Voice Feedback: ${voiceEnabled ? "ON" : "OFF"}`;
      if (voiceEnabled) speak("Voice feedback enabled");
    });

    colorBtn.addEventListener("click", () => {
      // Toggle the 'colorblind' CSS class on the body tag
      const enabled = document.body.classList.toggle("colorblind");
      localStorage.setItem("colorBlindEnabled", enabled);
      colorBtn.textContent = `Color-Blind Mode: ${enabled ? "ON" : "OFF"}`;
      if (voiceEnabled) speak(enabled ? "Color blind mode enabled" : "Color blind mode disabled");
    });

    /* (Web Speech API)
       Utilises the browser's built-in text-to-speech.
       It also updates the 'aria-live' region for users with physical screen readers.
    */
    function speak(text) {
      if (!voiceEnabled || !window.speechSynthesis) return;
      window.speechSynthesis.cancel(); // Stops any current speaking to avoid overlapping
      const msg = new SpeechSynthesisUtterance(text);
      window.speechSynthesis.speak(msg);
      ariaLive.textContent = text; 
    }

    /* INTERACTIVE CITY CARDS
       Loops through all city links to add a click event.
       It prevents the default instant link behavior to allow the voice feedback to play first.
    */
    document.querySelectorAll(".city").forEach(city => {
      city.addEventListener("click", (e) => {
        e.preventDefault(); // Stop the browser from following the link immediately
        const target = city.getAttribute('href');
        speak(`Navigating to ${city.querySelector("h2").textContent}`);
        
        // Wait 1 second for voice feedback before redirecting
        setTimeout(() => { window.location.href = target; }, 1000);
      });
    });
  </script>
</body>
</html>







