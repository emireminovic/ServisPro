<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        $user_id = $_GET['user_id'] ?? null;

        $sql = "SELECT o.*, u.naziv_firme AS user_naziv FROM obavestenja o JOIN users u ON o.user_id = u.id";
        $params = [];

        if ($user_id) {
            $sql .= " WHERE o.user_id = ?";
            $params[] = $user_id;
        }
        $sql .= " ORDER BY o.vreme_slanja DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['sadrzaj', 'user_id']);

        if (isset($data['sadrzaj']) && strlen($data['sadrzaj']) > 255) {
            $errors[] = "Sadržaj ne sme biti duži od 255 karaktera.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $stmt = $db->prepare("INSERT INTO obavestenja (sadrzaj, vreme_slanja, procitano, user_id) VALUES (?, NOW(), FALSE, ?)");
        $stmt->execute([$data['sadrzaj'], $data['user_id']]);

        jsonResponse(["message" => "Obaveštenje kreirano.", "id" => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);

        $data = getJsonInput();
        
        if (isset($data['mark_all_read']) && isset($data['user_id'])) {
            $stmt = $db->prepare("UPDATE obavestenja SET procitano = TRUE WHERE user_id = ?");
            $stmt->execute([$data['user_id']]);
            jsonResponse(["message" => "Sva obaveštenja označena kao pročitana."]);
        }

        $stmt = $db->prepare("UPDATE obavestenja SET procitano = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Obaveštenje nije pronađeno."], 404);
        jsonResponse(["message" => "Obaveštenje označeno kao pročitano."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM obavestenja WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Obaveštenje nije pronađeno."], 404);
        jsonResponse(["message" => "Obaveštenje obrisano."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
