<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function getJsonInput(): array {
    $input = json_decode(file_get_contents("php://input"), true);
    return $input ?? [];
}

function jsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function validateRequired(array $data, array $fields): array {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $errors[] = "Polje '{$field}' je obavezno.";
        }
    }
    return $errors;
}

function getRequestInfo(): array {
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    return [$method, $id];
}

function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}


function requireAuth(): array {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(["error" => "Niste prijavljeni."], 401);
    }
    return $user;
}


function requireRole(array $allowedRoles): array {
    $user = requireAuth();
    if (!in_array($user['uloga'], $allowedRoles)) {
        jsonResponse(["error" => "Nemate dozvolu za ovu akciju."], 403);
    }
    return $user;
}


function createNotification(PDO $db, int $userId, string $sadrzaj): void {
    $stmt = $db->prepare("INSERT INTO obavestenja (sadrzaj, vreme_slanja, procitano, user_id) VALUES (?, NOW(), FALSE, ?)");
    $stmt->execute([$sadrzaj, $userId]);
}
