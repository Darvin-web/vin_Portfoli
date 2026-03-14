<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove error reporting in production
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Remove test echo - causes header issues
    // echo "<!-- Database connected successfully -->";
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>