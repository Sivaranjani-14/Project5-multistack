<?php
/**
 * Database connection provider using PDO.
 * Extracts environmental variables injected cleanly via AWS Elastic Beanstalk.
 */
function getDBConnection(): PDO {
    $host    = $_SERVER['RDS_HOSTNAME'] ?? null;
    $dbName  = $_SERVER['RDS_DB_NAME']   ?? 'phpdb';
    $user    = $_SERVER['RDS_USERNAME']  ?? null;
    $password = $_SERVER['RDS_PASSWORD']  ?? null;
    $port    = $_SERVER['RDS_PORT']      ?? '3306';
    $charset = 'utf8mb4';

    if (!$host || !$user || !$password) {
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
        $pdo = new PDO($dsn, $user, $password, $options);
        
        // AUTOMATED SCHEMA INITIALIZATION CODE 👇
        $tableSchema = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $pdo->exec($tableSchema);
        
        return $pdo;
    } catch (\PDOException $e) {
        throw new \PDOException("Database connection established failed: " . $e->getMessage(), (int)$e->getCode());
    }
}