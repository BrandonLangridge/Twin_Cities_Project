<?php

$config = require __DIR__ . '/config.php';

try {

    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $config['db']['host'],
        $config['db']['name'],
        $config['db']['charset']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO(
        $dsn,
        $config['db']['user'],
        $config['db']['pass'],
        $options
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    trigger_error("Database connection failed.", E_USER_ERROR);
}