<?php
// api/resolve_task.php
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Task marked as resolved.']);