<?php
/* config.php */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Define Settings Array
$config = [
    "db" => [
        "host" => "localhost",
        "name" => "city_twin_db",
        "user" => "root",
        "pass" => "",
        "charset" => "utf8mb4" 
    ],

    "api" => [
        "weather_base_url" => "https://api.open-meteo.com/v1/",
        "weather_units"    => "metric"
    ],

    "queries" => [
        "get_twin_cities" => "
            SELECT 
                c1.name AS city_1, 
                c2.name AS city_2 
            FROM Cities c1 
            JOIN Cities c2 ON c1.country != c2.country 
            LIMIT 1
        " 
    ],

    "rss" => [
        "title"       => "Liverpool & Cologne - Places Feed",
        "description" => "Dynamic RSS generated from Cities and Place_of_Interest tables.",
        "base_url"    => "http://localhost/TwinCities", 
        "max_items"   => 50
    ]
];

// Establish PDO Connection
// This creates the $pdo object so other files can use it immediately.
try {
    $dsn = "mysql:host=" . $config['db']['host'] . ";dbname=" . $config['db']['name'] . ";charset=" . $config['db']['charset'];
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If the connection fails, show a clear message
    die("Global Config Error: Database Connection Failed - " . $e->getMessage());
}

// Return the array (for files that still need the settings)
return $config;