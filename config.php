<?php
// Core App Settings & PDO Database Connection Setup
// File: config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Standard XAMPP Defaults)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default password is blank in XAMPP
$db_name = 'bookstore';

// Include auto-initialization script
require_once __DIR__ . '/db_init.php';

// Programmatically verify & construct schema
init_database($db_host, $db_user, $db_pass, $db_name);

// Open active PDO Connection for this request
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper to keep user cart badge counts synced
function update_session_cart_count($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['cart_count'] = (int)$stmt->fetchColumn();
    } else {
        $_SESSION['cart_count'] = 0;
    }
}

// Automatically sync count
update_session_cart_count($pdo);
?>
