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
  scrollWheelZoom: false 
}).setView(activeCity.center, activeCity.zoom);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);

// POIs Rendering
if (typeof pois !== 'undefined') {
  pois
    .filter(poi => poi.town.toLowerCase() === cityKey)
    .forEach(poi => {
      const marker = L.marker([poi.lat, poi.lng]).addTo(map);

      const tooltipContent = `
        <div style="padding: 5px; min-width: 150px;">
          <strong style="color: #0b5fff;">${poi.name}</strong><br>
          <small>Type: ${poi.type}</small><br>
          <small>Opened: ${poi.yearOpened}</small><br>
          <small>Entry: ${poi.entryFee}</small><br>
          <strong>Rating: ${poi.rating} ★</strong>
        </div>
      `;

      marker.bindTooltip(tooltipContent, { direction: "top", sticky: true });

      // REDIRECT LOGIC
      marker.on("click", () => {
        /* Prepare the name for Wikipedia (replace spaces with underscores)
           Redirect to details.php with the 'poi' parameter */
        const wikiFormattedName = poi.name.replace(/\s+/g, '_');
        window.location.href = `details.php?poi=${wikiFormattedName}`;
      });
    });
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







