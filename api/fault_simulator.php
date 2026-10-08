<?php
// api/fault_simulator.php
header('Content-Type: application/json');
echo json_encode(['success' => true, 'status' => 'FAULT_INJECTOR_READY']);