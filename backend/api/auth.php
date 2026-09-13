<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = getCurrentUser();
    if ($user) {
        jsonResponse(["user" => $user]);
    }
    jsonResponse(["error" => "Niste prijavljeni."], 401);
}

if ($method === 'DELETE') {
    session_destroy();
    jsonResponse(["message" => "Uspešno ste se odjavili."]);
}

if ($method !== 'POST') {
    jsonResponse(["error" => "Metoda nije podržana."], 405);
}

$data = getJsonInput();
$errors = validateRequired($data, ['email', 'lozinka']);
if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

$stmt = $db->prepare("SELECT id, naziv_firme, adresa, email, lozinka, uloga FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch();

if (!$user || !password_verify($data['lozinka'], $user['lozinka'])) {
    jsonResponse(["error" => "Pogrešan email ili lozinka."], 401);
}

unset($user['lozinka']);
$_SESSION['user'] = $user;

jsonResponse(["message" => "Uspešna prijava.", "user" => $user]);
