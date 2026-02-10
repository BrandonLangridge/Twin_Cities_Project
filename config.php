<?php
// Configuration file holding application-wide data.

// 1. User Defined Error Function
function myErrorHandler($level, $message, $file, $line) {
    echo "<b>[Application Error]:</b> [$level] $message in $file on line $line<br>";
}
set_error_handler("myErrorHandler");

// 2. Configuration Data Array
return [
  "db" => [
    "host" => "localhost",
    "name" => "city_twin_db",
    "user" => "root",
    "pass" => "", 
    "charset" => "utf8mb4"
  ],

  "api" => [
    "weather_key" => "",
    "weather_units" => "metric",
    "weather_base_url" => "https://api.open-meteo.com/v1/"
  ],

  "rss" => [
    "title" => "Liverpool & Cologne - City News & Places",
    "description" => "Live updates and landmarks from our database.",
    "base_url" => "http://localhost/TwinCities", 
    "max_items" => 50
  ]
];