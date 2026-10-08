<?php
// api/fabric_gateway.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'sign') {
    $party = $input['party'] ?? 'buyer';
    $txHash = '0x' . hash('sha256', microtime() . $party);

    $pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)")
        ->execute([1, 'CRYPTO_KEY_SIGNED', "Signature committed by {$party}. Commit: {$txHash}"]);

    echo json_encode([
        'success' => true,
        'party' => $party,
        'tx_hash' => $txHash,
        'state' => 'COMMITTED'
    ]);
    exit;
}

echo json_encode(['success' => true, 'block_height' => 10842]);