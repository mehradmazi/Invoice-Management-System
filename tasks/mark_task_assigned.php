<?php
session_start();
header('Content-Type: application/json');

include(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$task_id = (int) $_GET['id'];

$stmt = $pdo->prepare("UPDATE tracked_tasks SET invoice_id = -1 WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Task not found or not owned by user']);
    exit;
}

http_response_code(200);
echo json_encode(['status' => 'ok']);