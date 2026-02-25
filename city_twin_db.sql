
DROP DATABASE IF EXISTS city_twin_db;
CREATE DATABASE city_twin_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE city_twin_db;


-- Table: Cities
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


-- Table: Comments
CREATE TABLE Comments (
    comments_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    comment_text TEXT NOT NULL,
    search_query VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    city_id INT NOT NULL,
    CONSTRAINT fk_comments_city
        FOREIGN KEY (city_id)
        REFERENCES Cities(city_id)
        ON DELETE CASCADE,
    INDEX (search_query)
) ENGINE=InnoDB;


-- Table: Place_of_Interest
CREATE TABLE Place_of_Interest (
    poi_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    description TEXT NULL,
    city_id INT NOT NULL,
    CONSTRAINT fk_poi_city
        FOREIGN KEY (city_id)
        REFERENCES Cities(city_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- Table: Images
CREATE TABLE Images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(2048) NOT NULL,
    caption VARCHAR(255) NULL,
    poi_id INT NOT NULL,
    CONSTRAINT fk_images_poi
        FOREIGN KEY (poi_id)
        REFERENCES Place_of_Interest(poi_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
