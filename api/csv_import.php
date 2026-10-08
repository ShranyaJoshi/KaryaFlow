<?php
// api/csv_import.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$records = [
    ['invoice_id' => 'INV-1042', 'vendor' => 'Apex Cloud Logistics', 'amount' => 17250.00, 'due_days' => 44, 'status' => 'OVERDUE'],
    ['invoice_id' => 'INV-1043', 'vendor' => 'Global Freight Corp', 'amount' => 8420.50, 'due_days' => 12, 'status' => 'CURRENT'],
    ['invoice_id' => 'INV-1044', 'vendor' => 'Zenith Tech Systems', 'amount' => 24100.00, 'due_days' => 38, 'status' => 'OVERDUE'],
    ['invoice_id' => 'INV-1045', 'vendor' => 'Quantum Data Networks', 'amount' => 5200.00, 'due_days' => 5, 'status' => 'CURRENT']
];

echo json_encode(['success' => true, 'records' => $records]);