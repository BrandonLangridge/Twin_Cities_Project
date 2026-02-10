// maps.js

const params = new URLSearchParams(window.location.search);
const cityParam = params.get("city");

const cities = {
  liverpool: {
    name: "Liverpool",
    center: [53.4084, -2.9916],
    zoom: 13
  },
  cologne: {
    name: "Cologne",
    center: [50.9413, 6.9583],
    zoom: 13
  }
};

if (!cities[cityParam]) {
  document.body.innerHTML = "<h2>City not found</h2>";
  throw new Error("Invalid city parameter");
}

const cityName = cities[cityParam].name;

// Header + browser tab title
document.title = `${cityName} Map | Twin Cities`;
document.getElementById("city-title").innerText = `${cityName} Map`;

// Back button (falls back to index if no history)
document.getElementById("back-btn").addEventListener("click", () => {
  if (history.length > 1) history.back();
  else window.location.href = "index.html";
});

const map = L.map("map").setView(
  cities[cityParam].center,
  cities[cityParam].zoom
);

L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: "&copy; OpenStreetMap contributors"
}).addTo(map);

pois
  .filter(poi => poi.town === cityName)
  .forEach(poi => {
    const marker = L.marker([poi.lat, poi.lng]).addTo(map);

    // Hover tooltip
    marker.bindTooltip(
      `
      <strong>${poi.name}</strong><br>
      Type: ${poi.type}<br>
      Opened: ${poi.yearOpened}<br>
      Entry: ${poi.entryFee}<br>
      Rating: ${poi.rating}
      `,
      {
        direction: "top",
        sticky: true,
        opacity: 1
      }
    );

    // Force tooltip behavior
    marker.on("mouseover", function () {
      this.openTooltip();
    });

    marker.on("mouseout", function () {
      this.closeTooltip();
    });

    // Click redirects to details page
    marker.on("click", () => {
      window.location.href = `details.html?poi=${encodeURIComponent(poi.name)}`;
    });
  });








