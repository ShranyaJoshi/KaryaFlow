<?php
// api/datasources.php
header('Content-Type: application/json');
echo json_encode([
    'sources' => [
        ['name' => 'SAP S/4HANA Ledger', 'status' => 'CONNECTED', 'ping_ms' => 24],
        ['name' => 'Oracle NetSuite ERP', 'status' => 'CONNECTED', 'ping_ms' => 31],
        ['name' => 'Consortium Fabric Node', 'status' => 'ONLINE', 'ping_ms' => 8]
    ]
]);