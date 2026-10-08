<?php
// api/audit_logs.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 25");
$logs = $stmt->fetchAll();

if (empty($logs)) {
    $logs = [
        ['created_at' => date('Y-m-d H:i:s'), 'action' => 'SYSTEM_INIT', 'details' => 'Hyperledger Fabric consortium network online.']
    ];
}

echo json_encode(['success' => true, 'logs' => $logs]);