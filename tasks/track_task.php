<?php
session_start();
header('Content-Type: application/json');

include(__DIR__ . '/../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'unauthorized', 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$description = trim($data['description'] ?? '');
$start = $data['start_time'] ?? null;
$end = $data['end_time'] ?? null;
$hours = $data['hours'] ?? null;

if (!$description || !$start || !$end || !$hours) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO tracked_tasks (user_id, task_description, start_time, end_time, hours) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $description,
        $start,
        $end,
        $hours
    ]);

    echo json_encode(['status' => 'success', 'id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}