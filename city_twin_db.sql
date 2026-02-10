-- ============================
-- City Twin Database
-- ============================

-- Drop database if it already exists (optional, useful for re-running)
DROP DATABASE IF EXISTS city_twin_db;

-- Create database
CREATE DATABASE city_twin_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Use the database
USE city_twin_db;

-- ============================
-- Table: City
-- ============================
CREATE TABLE City (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    population INT,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    currency VARCHAR(50),
    description TEXT,

    UNIQUE (name, country)
) ENGINE=InnoDB;

-- ============================
-- Table: Twin_Relationship
-- ============================
CREATE TABLE Twin_Relationship (
    twin_id INT AUTO_INCREMENT PRIMARY KEY,
    established_year YEAR,
    notes TEXT,

    city_id_1 INT NOT NULL,
    city_id_2 INT NOT NULL,

    CONSTRAINT fk_twin_city_1
        FOREIGN KEY (city_id_1)
        REFERENCES City(city_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_twin_city_2
        FOREIGN KEY (city_id_2)
        REFERENCES City(city_id)
        ON DELETE CASCADE,

    CONSTRAINT chk_different_cities
        CHECK (city_id_1 <> city_id_2),

    UNIQUE (city_id_1, city_id_2)
) ENGINE=InnoDB;

-- ============================
-- Table: Place_of_Interest
-- ============================
CREATE TABLE Place_of_Interest (
    place_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(50),
    capacity INT,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    description TEXT,

    city_id INT NOT NULL,

    CONSTRAINT fk_place_city
        FOREIGN KEY (city_id)
        REFERENCES City(city_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================
-- Table: Photo
-- ============================
CREATE TABLE Photo (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    caption VARCHAR(255),

    place_id INT NOT NULL,

    CONSTRAINT fk_photo_place
        FOREIGN KEY (place_id)
        REFERENCES Place_of_Interest(place_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
