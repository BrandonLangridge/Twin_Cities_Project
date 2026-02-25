<?php
/* weather_widget.php */

/* RENDER FUNCTION
   Handles API request with unit mapping */
function renderWeatherWidget($cityName, $lat, $lon, $weatherBase, $units, $weatherCodes) {
    
    // DATA MAPPING
    // Mapping from 'metric' to 'celsius' to combat any errors
    $apiUnits = ($units === 'metric') ? 'celsius' : (($units === 'imperial') ? 'fahrenheit' : $units);

    $baseUrl = rtrim($weatherBase, '/');
    $queryParams = [
        'current_weather'  => 'true', 
        'latitude'         => $lat,
        'longitude'        => $lon,
        'daily'            => 'temperature_2m_max,temperature_2m_min,sunrise,sunset,weathercode,precipitation_sum',
        'timezone'         => 'auto',
        'forecast_days'    => 7,
        'temperature_unit' => $apiUnits 
    ];

    // PHP_QUERY_RFC3986 prevents the &cur -> ¤ symbol conversion
    $url = $baseUrl . "/forecast?" . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);

    // FETCH DATA (cURL)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // ERROR HANDLING
    if (!$data || !isset($data["daily"])) {
        echo "<div class='error-msg'>";
        echo "<strong>Notice:</strong> Weather data currently unavailable for " . htmlspecialchars($cityName) . ".";
        echo "</div>";
        return;
    }

    // RENDERING THE WIDGET
    ?>
    <article class="city" aria-labelledby="label-<?= htmlspecialchars($cityName) ?>">
        <h2 id="label-<?= htmlspecialchars($cityName) ?>">Weather for <?= htmlspecialchars($cityName) ?></h2>
        
        <?php if (isset($data["current_weather"])): $current = $data["current_weather"]; ?>
            <dl class="current-weather-list">
                <div>
                    <dt>Current Temp:</dt>
                    <dd><?= $current['temperature'] ?>&deg;<?= ($units === 'imperial' ? 'F' : 'C') ?></dd>
                </div>
                <div>
                    <dt>Wind Speed:</dt>
                    <dd><?= $current['windspeed'] ?> km/h</dd>
                </div>
                <div>
                    <dt>Weather:</dt>
                    <dd><?= $weatherCodes[$current['weathercode']] ?? 'Clear' ?></dd>
                </div>
            </dl>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>High</th>
                        <th>Low</th>
                        <th>Description</th>
                        <th>Rain</th>
                        <th>Sunrise</th>
                        <th>Sunset</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $daily = $data["daily"];
                    for ($i = 0; $i < count($daily["time"]); $i++): 
                        $day = date("D, j M", strtotime($daily["time"][$i]));
                    ?>
                        <tr>
                            <td class="left-edge date"><?= $day ?></td>
                            <td class="high"><?= number_format($daily["temperature_2m_max"][$i], 1) ?>&deg;</td>
                            <td class="low"><?= number_format($daily["temperature_2m_min"][$i], 1) ?>&deg;</td>
                            <td class="weather-desc"><?= $weatherCodes[$daily["weathercode"][$i]] ?? 'Clear' ?></td>
                            <td class="precip-cell"><?= number_format($daily["precipitation_sum"][$i], 1) ?>mm</td>
                            <td class="rise"><?= date("H:i", strtotime($daily["sunrise"][$i])) ?></td>
                            <td class="set right-edge"><?= date("H:i", strtotime($daily["sunset"][$i])) ?></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </article>
    <?php
}

// SHARED WEATHER CODES
$weatherCodes = [
    0 => "Clear sky", 1 => "Mainly clear", 2 => "Partly cloudy", 3 => "Overcast",
    45 => "Fog", 48 => "Rime fog", 51 => "Drizzle: Light",
    53 => "Drizzle: Moderate", 55 => "Drizzle: Dense", 61 => "Rain: Slight",
    63 => "Rain: Moderate", 65 => "Rain: Heavy", 80 => "Rain showers",
    95 => "Thunderstorm", 96 => "Thunderstorm/Hail", 99 => "Heavy Thunderstorm"
];





