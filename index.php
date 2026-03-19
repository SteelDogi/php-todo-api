<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'Task.php';

$taskModel = new Task();
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = rtrim($uri, '/');


$parts = explode('/', $uri);

$taskIndex = array_search('tasks', $parts);

if ($taskIndex === false) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit();
}


$id = $parts[$taskIndex + 1] ?? null;

$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $task = $taskModel->getById($id);
                if ($task) {
                    echo json_encode($task);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                }
            } else {
                $tasks = $taskModel->getAll();
                echo json_encode($tasks);
            }
            break;

        case 'POST':
            if (empty($input['title'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title is required']);
                exit();
            }

            $newId = $taskModel->create($input);
            http_response_code(201);
            echo json_encode(['id' => $newId, 'message' => 'Task created']);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required for update']);
                exit();
            }
            if (empty($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'No data to update']);
                exit();
            }
            if (isset($input['title']) && empty($input['title'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Title cannot be empty']);
                exit();
            }

            $updated = $taskModel->update($id, $input);
            if ($updated) {
                echo json_encode(['message' => 'Task updated']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found or no changes']);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required for delete']);
                exit();
            }

            $deleted = $taskModel->delete($id);
            if ($deleted) {
                http_response_code(204); // No Content
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}