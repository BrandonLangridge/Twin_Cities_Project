<?php
/* setup.php */
ob_start(); // Start output buffering
require_once 'config.php';

$host = $config['db']['host'];
$user = $config['db']['user'];
$pass = $config['db']['pass'];
$dbname = $config['db']['name'];

try {
    // 1. Connect to MySQL server (without selecting DB)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create Database
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    $pdo->exec("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // 3. Define Tables
    $tables = "
    CREATE TABLE Cities (
        city_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        population INT NOT NULL,
        latitude DECIMAL(10,8) NOT NULL,
        longitude DECIMAL(11,8) NOT NULL,
        currency VARCHAR(4) NOT NULL,
        description TEXT NULL,
        UNIQUE (name, country)
    ) ENGINE=InnoDB;

    CREATE TABLE Comments (
        comments_id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(100) NOT NULL,
        comment_text TEXT NOT NULL,
        search_query VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
        city_id INT NOT NULL,
        CONSTRAINT fk_comments_city FOREIGN KEY (city_id) REFERENCES Cities(city_id) ON DELETE CASCADE,
        INDEX (search_query)
    ) ENGINE=InnoDB;

    CREATE TABLE Place_of_Interest (
        poi_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        type VARCHAR(50) NOT NULL,
        capacity INT NULL,
        latitude DECIMAL(10,8) NOT NULL,
        longitude DECIMAL(11,8) NOT NULL,
        description TEXT NULL,
        city_id INT NOT NULL,
        CONSTRAINT fk_poi_city FOREIGN KEY (city_id) REFERENCES Cities(city_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE Images (
        image_id INT AUTO_INCREMENT PRIMARY KEY,
        image_url VARCHAR(2048) NOT NULL,
        caption VARCHAR(255) NULL,
        poi_id INT NOT NULL,
        CONSTRAINT fk_images_poi FOREIGN KEY (poi_id) REFERENCES Place_of_Interest(poi_id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    -- NEW TABLE: Photos
    CREATE TABLE Photos (
        photo_id INT AUTO_INCREMENT PRIMARY KEY,
        city_id VARCHAR(50) NOT NULL,
        page_num INT NOT NULL,
        image_url VARCHAR(2048) NOT NULL,
        caption VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($tables);

    // 4. Insert Initial Cities
    $citySql = "
    INSERT INTO Cities (city_id, name, country, population, latitude, longitude, currency, description) VALUES
        (1, 'Liverpool', 'UK', 496784, 53.4084, -2.9916, 'GBP', 'A maritime city in northwest England.'),
        (2, 'Cologne', 'Germany', 1086000, 50.9375, 6.9603, 'EUR', 'A 2,000-year-old city spanning the Rhine River.');
    ";
    $pdo->exec($citySql);

    // 5. Seed Photos table (optional, empty initially)
    $photoSeed = "
    INSERT INTO Photos (city_id, page_num, image_url, caption) VALUES
        ('1', 1, 'https://example.com/liverpool1.jpg', 'Liverpool photo 1'),
        ('2', 1, 'https://example.com/cologne1.jpg', 'Cologne photo 1');
    ";
    $pdo->exec($photoSeed);

    // 6. Insert Place of Interest (POIs)
    $poiSql = "
    INSERT INTO Place_of_Interest (name, type, capacity, latitude, longitude, description, city_id) VALUES
    ('The Beatles Story', 'Museum', NULL, 53.39930300, -2.99206600, 'Museum dedicated to the life and music of The Beatles.', 1),
    ('Liverpool Cathedral', 'Religious Site', 2200, 53.39744600, -2.97317000, 'The largest cathedral in the UK.', 1),
    ('Cologne Cathedral', 'Religious Site', 20000, 50.94133400, 6.95813300, 'Gothic Roman Catholic cathedral and UNESCO World Heritage Site.', 2),
    ('Museum Ludwig', 'Museum', NULL, 50.94084900, 6.96003700, 'Museum of modern and contemporary art.', 2);
    ";
    $pdo->exec($poiSql);

} catch (PDOException $e) {
    die("Setup Failed: " . $e->getMessage());
}

header("Location: index.php");
exit;