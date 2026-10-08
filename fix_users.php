<?php
// fix_users.php - One-click table migration
require_once __DIR__ . '/config/db.php';

try {
    // 1. Rebuild or alter the users table to ensure required columns exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('FINANCE_OPERATOR', 'CFO_APPROVER', 'AUDITOR') DEFAULT 'FINANCE_OPERATOR',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");

    // 2. Check if 'name' column exists; if not, add it
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'name'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL AFTER id");
        echo "Added 'name' column successfully.<br>";
    }

    // 3. Check if 'role' column exists; if not, add it
    $roleCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    if (!$roleCol) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('FINANCE_OPERATOR', 'CFO_APPROVER', 'AUDITOR') DEFAULT 'FINANCE_OPERATOR' AFTER password_hash");
        echo "Added 'role' column successfully.<br>";
    }

    echo "<h3>Users table successfully updated to support registration!</h3>";
} catch (Exception $e) {
    echo "<h3>Migration Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}