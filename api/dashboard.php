<?php
// api/dashboard.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

echo json_encode([
    'success' => true,
    'stats' => [
        'total_workflows' => 8,
        'autonomous_success_rate' => '98.4%',
        'total_settled_usd' => 142500.00,
        'replan_count' => 3
    ]
]);