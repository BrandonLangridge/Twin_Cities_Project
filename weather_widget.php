<?php

// Array for cities with their latitude and longitude
$cities = [
    "Liverpool" => ["lat" => 53.4106, "lon" => -2.9779],
    "Cologne"   => ["lat" => 50.9333, "lon" => 6.95]
];

// Array matching weather codes to descriptions
$weatherCodes = [
    0   => "Clear sky",
    1   => "Mainly clear",
    2   => "Partly cloudy",
    3   => "Overcast",
    45  => "Fog",
    48  => "Depositing rime fog",
    51  => "Drizzle: Light intensity",
    53  => "Drizzle: Moderate intensity",
    55  => "Drizzle: Dense intensity",
    56  => "Freezing Drizzle: Light intensity",
    57  => "Freezing Drizzle: Dense intensity",
    61  => "Rain: Slight intensity",
    63  => "Rain: Moderate intensity",
    65  => "Rain: Heavy intensity",
    66  => "Freezing Rain: Light intensity",
    67  => "Freezing Rain: Heavy intensity",
    71  => "Snow fall: Slight intensity",
    73  => "Snow fall: Moderate intensity",
    75  => "Snow fall: Heavy intensity",
    77  => "Snow grains",
    80  => "Rain showers: Slight",
    81  => "Rain showers: Moderate",
    82  => "Rain showers: Violent",
    85  => "Snow showers: Slight",
    86  => "Snow showers: Heavy",
    95  => "Thunderstorm: Slight or moderate",
    96  => "Thunderstorm with slight hail",
    99  => "Thunderstorm with heavy hail"
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather Forecast</title>
    <link rel="stylesheet" href="weather_style.css">
</head>

<body class="weather-dashboard">

<header>
    <button onclick="window.location.href='index.php'"
            aria-label="Return to the main index page">
        ← Back
    </button>

    <button id="colorToggle">Colour-Blind Mode: OFF</button>
</header>

<main> 
<?php

// Loop through each city to get and display its weather data
foreach ($cities as $cityName => $coords) {

    // API URL requesting: 
    // Daily max and min temps 
    // Daily sunrise/sunset times (timezone adjusted)
    // Daily weathercode to be matched to description
    // Daily precipitation
    // Current temp, wind and weather type

    $url = "https://api.open-meteo.com/v1/forecast?latitude={$coords['lat']}&longitude={$coords['lon']}&daily=temperature_2m_max,temperature_2m_min,sunrise,sunset,weathercode,precipitation_sum&current_weather=true&timezone=auto&forecast_days=7";

    // Fetch data as a string
    $response = file_get_contents($url);

    // Convert string to a PHP array
    $data = json_decode($response, true);

    // Aria label for screenreaders
    echo "<article class='city' aria-labelledby='label-$cityName'>";
    echo "<h2 id='label-$cityName'>Weather for $cityName</h2>";

    // Show current weather if available, using screenreader friendly semantics
    if (isset($data["current_weather"])) {
        $current = $data["current_weather"];

        echo "<dl class='current-weather-list'>";
            echo "<div>";
                echo "<dt>Current Temp:</dt>";
                echo "<dd>{$current['temperature']}&deg;C</dd>";
            echo "</div>";
            echo "<div>";
                echo "<dt>Wind Speed:</dt>";
                echo "<dd>{$current['windspeed']} km/h</dd>";
            echo "</div>";
            echo "<div>";
                echo "<dt>Weather:</dt>";
                echo "<dd>{$weatherCodes[$current['weathercode']]}</dd>";
            echo "</div>";
        echo "</dl>";
    }

    // Check if daily forecast is available
    if (!isset($data["daily"])) {
        echo "<p>Unable to fetch daily forecast.</p></article>";
        continue; // Skip to the next city
    }

    // Create forecast table
    echo "<table>";
    // Caption tag for screenreaders (hidden visually via CSS)
    echo "<caption class='sr-only'>7-day weather forecast for $cityName</caption>";
    echo "<thead>";

    // Column scope for screenreaders
    echo "<tr>
            <th scope='col'>Forecast Date</th>
            <th scope='col'>High</th>
            <th scope='col'>Low</th>
            <th scope='col'>Weather Description</th>
            <th scope='col'>Precipitation</th>
            <th scope='col'>Sunrise</th>
            <th scope='col'>Sunset</th>
          </tr>
        </thead>
        <tbody>";

    // Loop through each day in the forecast using the number of time entries
    for ($i = 0; $i < count($data["daily"]["time"]); $i++) {

        // Convert the raw API date into a readable fromat of weekday/date/month abbreviation
        $rawDate = $data["daily"]["time"][$i];
        $day = date("D, j M", strtotime($rawDate));

        // Convert sunrise/sunset into hour:minute format
        $rise = date("H:i", strtotime($data["daily"]["sunrise"][$i]));
        $set  = date("H:i", strtotime($data["daily"]["sunset"][$i]));

        // Get daily weather code
        $dailyWeatherCode = $data["daily"]["weathercode"][$i];
        // Get daily precipitation and force 1 decimal place
        $dailyPrecip = number_format($data["daily"]["precipitation_sum"][$i], 1);

        // Create row for the day and populate it with the data
        echo "<tr>";
            // Time tags for screenreaders
            echo "<td class='left-edge date'><time datetime='$rawDate'>$day</time></td>";
            // High and low temps forced to one decimal point 
            echo "<td class='high'>" . number_format($data["daily"]["temperature_2m_max"][$i], 1) . "°C</td>";
            echo "<td class='low'>" . number_format($data["daily"]["temperature_2m_min"][$i], 1) . "°C</td>";
            // Weather codes selecting description from the array 
            echo "<td class='weather-desc'>{$weatherCodes[$dailyWeatherCode]}</td>";
            echo "<td class='precip-cell'>{$dailyPrecip}mm</td>";
            echo "<td class='rise'>$rise</td>";
            echo "<td class='set right-edge'>$set</td>";
        echo "</tr>";
    }

    echo "</tbody></table></article>";
}

?>
</main> 

<script>
    // Retrieve saved colour blind setting
    let colorBlindEnabled = localStorage.getItem("colorBlindEnabled") === "true";
    const colorBtn = document.getElementById("colorToggle");

    // Apply saved colour blind mode
    if (colorBlindEnabled) document.body.classList.add("colorblind");

    // Update button label
    colorBtn.textContent = `Colour-Blind Mode: ${colorBlindEnabled ? "ON" : "OFF"}`;

    // Colour blind toggle
    colorBtn.addEventListener("click", () => {
        colorBlindEnabled = document.body.classList.toggle("colorblind");
        localStorage.setItem("colorBlindEnabled", colorBlindEnabled);
        colorBtn.textContent = `Colour-Blind Mode: ${colorBlindEnabled ? "ON" : "OFF"}`;
    });
</script>

</body>
</html>





