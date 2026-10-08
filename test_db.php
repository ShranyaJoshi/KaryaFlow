<?php
// test_db.php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json');
try {
    $stmt = $pdo->query("SELECT 'Database connection successful!' AS message");
    echo json_encode(['success' => true, 'result' => $stmt->fetch()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}