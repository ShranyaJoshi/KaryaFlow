<?php
// api/workflow_engine.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../engine/WorkflowEngine.php';
require_once __DIR__ . '/../engine/ReplanningEngine.php';

$engine = new WorkflowEngine($pdo);
$replanner = new ReplanningEngine($pdo);

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'synthesize') {
    $title = $input['title'] ?? 'Synthesized Enterprise Pipeline';
    $obj   = $input['objective'] ?? 'Reconciliation workflow';
    $prio  = $input['priority'] ?? 'HIGH';
    $mode  = $input['execution_mode'] ?? 'ASSISTED';
    $owner = $input['owner'] ?? 'finance-lead@karyaflow.internal';

    $id = $engine->synthesize($title, $obj, $prio, $mode, $owner);
    echo json_encode(['success' => true, 'workflow_id' => $id]);
    exit;
}

if ($action === 'advance') {
    $wfId = intval($input['workflow_id'] ?? 1);
    $engine->advance($wfId);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'manage_failed_task') {
    $wfId   = intval($input['workflow_id'] ?? 1);
    $stepId = intval($input['step_id'] ?? 2);
    $res    = $input['resolution'] ?? 'retry';

    $replanner->replan($wfId, $stepId, $res);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'simulate_event') {
    $wfId  = intval($input['workflow_id'] ?? 1);
    $event = $input['event_type'] ?? 'PAYMENT_RECEIVED';

    $pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)")
        ->execute([$wfId, 'SIMULATION_TRIGGERED', "Shock event injected: {$event}"]);
    echo json_encode(['success' => true]);
    exit;
}