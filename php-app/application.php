<?php
/**
 * Database connection provider using PDO.
 * Extracts environmental variables injected cleanly via AWS Elastic Beanstalk.
 */
function getDBConnection(): PDO {
    // Read Beanstalk application configuration keys
    $host    = $_SERVER['RDS_HOSTNAME'] ?? null;
    $dbName  = $_SERVER['RDS_DB_NAME']   ?? 'phpdb';
    $user    = $_SERVER['RDS_USERNAME']  ?? null;
    $password = $_SERVER['RDS_PASSWORD']  ?? null;
    $port    = $_SERVER['RDS_PORT']      ?? '3306';
    $charset = 'utf8mb4';

    // Fail gracefully if variables aren't injected yet (local dev safe fallbacks)
    if (!$host || !$user || !$password) {
        // Fallback for local development if environment context is missing
        $host = $host ?? '127.0.0.1';
        $user = $user ?? 'root';
        $password = $password ?? '';
    }

    $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset;port=$port";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $password, $options);
    } catch (\PDOException $e) {
        // Log errors securely in a real production stack; display readable message for now
        throw new \PDOException("Database connection established failed: " . $e->getMessage(), (int)$e->getCode());
    }
}