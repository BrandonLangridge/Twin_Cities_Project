/* maps.js */

const params = new URLSearchParams(window.location.search);
const rawCity = params.get("city") || "Liverpool";
const cityKey = rawCity.toLowerCase();

const cities = {
  liverpool: { name: "Liverpool", center: [53.4084, -2.9916], zoom: 13 },
  cologne: { name: "Cologne", center: [50.9413, 6.9583], zoom: 13 }
};

if (!cities[cityKey]) {
  document.getElementById("map").innerHTML = "<h2>City not found</h2>";
  throw new Error("Invalid city");
}

const activeCity = cities[cityKey];

// Initialise Map with scroll fix
const map = L.map("map", {
  scrollWheelZoom: false,
  zoomControl: false,      // Disable +/- buttons for the cut-out look
  doubleClickZoom: false,  // Disable double-click zoom
  touchZoom: false,        // Disable touch/pinch zoom
  boxZoom: false           // Disable box zoom
}).setView(activeCity.center, activeCity.zoom);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);

// POIs Rendering
if (typeof pois !== 'undefined') {
  const markerArray = []; // To store markers for auto-fitting

  pois
    .filter(poi => poi.town.toLowerCase() === cityKey)
    .forEach(poi => {
      const marker = L.marker([poi.lat, poi.lng]).addTo(map);
      markerArray.push(marker); // Add to array for boundary calculation

      const tooltipContent = `
        <div style="padding: 5px; min-width: 150px;">
          <strong style="color: #0b5fff;">${poi.name}</strong><br>
          <small>Type: ${poi.type}</small><br>
          <small>Opened: ${poi.yearOpened}</small><br>
          <small>Entry: ${poi.entryFee}</small><br>
          <strong>Rating: ${poi.rating} ★</strong>
        </div>
      `;

      // Updated with boundary: 'viewport' to prevent clipping issues
      marker.bindTooltip(tooltipContent, { 
        direction: "top", 
        sticky: true,
        boundary: 'viewport' 
      });

      // REDIRECT LOGIC
      marker.on("click", () => {
        /* Prepare the name for Wikipedia (replace spaces with underscores)
           Redirect to details.php with the 'poi' parameter */
        const wikiFormattedName = poi.name.replace(/\s+/g, '_');
        window.location.href = `details.php?poi=${wikiFormattedName}`;
      });
    });

  /* AUTO-FIT LOGIC 
     Ensures all POIs are visible on the static map */
  if (markerArray.length > 0) {
    const group = new L.featureGroup(markerArray);
    map.fitBounds(group.getBounds(), { padding: [50, 50] }); 
  }
}

/* RESET LOGIC
   Ensures the map renders correctly and tooltips are cleared when navigating */
window.addEventListener('pageshow', function(event) {
    if (map) {
        map.closePopup();
        map.eachLayer(function(layer) {
            if (layer.closeTooltip) {
                layer.closeTooltip();
            }
        });
        map.invalidateSize();
    }
});







