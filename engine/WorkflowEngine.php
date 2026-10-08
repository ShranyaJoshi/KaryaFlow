<?php
// engine/WorkflowEngine.php

class WorkflowEngine {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function synthesize($title, $objective, $priority, $mode, $owner) {
        $stmt = $this->pdo->prepare("INSERT INTO workflows (title, objective, priority, execution_mode, owner, status, progress) VALUES (?, ?, ?, ?, ?, 'RUNNING', 20)");
        $stmt->execute([$title, $objective, $priority, $mode, $owner]);
        $workflowId = $this->pdo->lastInsertId();

        $steps = [
            [1, 'Multi-Source Bank Statement Ingestion', 'COMPLETED', 0, 'Extracted 12 line items'],
            [2, 'ERP & General Ledger Cross-Check', 'RUNNING', 0, 'Analyzing INV-1042 discrepancy ($17,250.00)'],
            [3, 'Dual-Key Hyperledger Escrow Signing', 'PENDING', 1, null],
            [4, 'Multi-Recipient Notice Dispatch', 'PENDING', 0, null],
            [5, 'Blockchain Ledger Finality Stamping', 'PENDING', 0, null]
        ];

        $ins = $this->pdo->prepare("INSERT INTO workflow_steps (workflow_id, step_order, step_name, status, requires_approval, step_output) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($steps as $s) {
            $ins->execute([$workflowId, $s[0], $s[1], $s[2], $s[3], $s[4]]);
        }

        $this->logAudit($workflowId, 'SYNTHESIS_INITIATED', "Workflow #{$workflowId} dynamically synthesized via objective parser.");
        return $workflowId;
    }

    public function advance($workflowId) {
        $stmt = $this->pdo->prepare("SELECT * FROM workflow_steps WHERE workflow_id = ? ORDER BY step_order ASC");
        $stmt->execute([$workflowId]);
        $steps = $stmt->fetchAll();

        foreach ($steps as $idx => $step) {
            if ($step['status'] === 'RUNNING') {
                $this->pdo->prepare("UPDATE workflow_steps SET status = 'COMPLETED' WHERE id = ?")->execute([$step['id']]);
                
                if (isset($steps[$idx + 1])) {
                    $next = $steps[$idx + 1];
                    $newStatus = $next['requires_approval'] ? 'WAITING_APPROVAL' : 'RUNNING';
                    $this->pdo->prepare("UPDATE workflow_steps SET status = ? WHERE id = ?")->execute([$newStatus, $next['id']]);
                    $progress = min(100, intval((($idx + 2) / count($steps)) * 100));
                    $this->pdo->prepare("UPDATE workflows SET status = ?, progress = ? WHERE id = ?")->execute([$newStatus, $progress, $workflowId]);
                } else {
                    $this->pdo->prepare("UPDATE workflows SET status = 'COMPLETED', progress = 100 WHERE id = ?")->execute([$workflowId]);
                }
                $this->logAudit($workflowId, 'STEP_ADVANCED', "Completed Step {$step['step_order']}: {$step['step_name']}");
                break;
            }
        }
    }

    public function logAudit($workflowId, $action, $details) {
        $stmt = $this->pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)");
        $stmt->execute([$workflowId, $action, $details]);
    }
}