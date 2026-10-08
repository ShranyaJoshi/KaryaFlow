<?php
// engine/ReplanningEngine.php

class ReplanningEngine {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function replan($workflowId, $failedStepId, $strategy) {
        if ($strategy === 'retry') {
            $stmt = $this->pdo->prepare("UPDATE workflow_steps SET status = 'RUNNING', error_message = NULL WHERE id = ?");
            $stmt->execute([$failedStepId]);
            $action = 'RETRY_INITIATED';
        } elseif ($strategy === 'skip') {
            $stmt = $this->pdo->prepare("UPDATE workflow_steps SET status = 'COMPLETED', step_output = 'Skipped via policy override' WHERE id = ?");
            $stmt->execute([$failedStepId]);
            $action = 'STEP_SKIPPED';
        } elseif ($strategy === 'replan') {
            $stmt = $this->pdo->prepare("UPDATE workflow_steps SET step_name = 'Alternate Routing & Arbitration Bridge', status = 'RUNNING', error_message = NULL WHERE id = ?");
            $stmt->execute([$failedStepId]);
            $action = 'DAG_REPLANNED';
        } else {
            $stmt = $this->pdo->prepare("UPDATE workflow_steps SET status = 'WAITING_APPROVAL', error_message = 'Escalated to CFO' WHERE id = ?");
            $stmt->execute([$failedStepId]);
            $action = 'ESCALATED_TO_CFO';
        }

        $audit = $this->pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)");
        $audit->execute([$workflowId, $action, "Replanning strategy applied: {$strategy} on step #{$failedStepId}"]);
        return true;
    }
}