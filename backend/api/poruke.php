<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        $user_id = $_GET['user_id'] ?? null;
        if (!$user_id) jsonResponse(["error" => "Parametar user_id je obavezan."], 400);

        $stmt = $db->prepare("
            SELECT p.*,
                   s.naziv_firme AS sender_naziv,
                   r.naziv_firme AS receiver_naziv
            FROM poruke p
            JOIN users s ON p.sender_id = s.id
            JOIN users r ON p.receiver_id = r.id
            WHERE p.sender_id = ? OR p.receiver_id = ?
            ORDER BY p.vreme_slanja DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['sadrzaj', 'sender_id', 'receiver_id']);

        if (isset($data['sadrzaj']) && strlen($data['sadrzaj']) > 255) {
            $errors[] = "Sadržaj poruke ne sme biti duži od 255 karaktera.";
        }
        if (isset($data['sender_id'], $data['receiver_id']) && $data['sender_id'] == $data['receiver_id']) {
            $errors[] = "Ne možete slati poruku samom sebi.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $stmt = $db->prepare("INSERT INTO poruke (sadrzaj, vreme_slanja, procitano, sender_id, receiver_id) VALUES (?, NOW(), FALSE, ?, ?)");
        $stmt->execute([$data['sadrzaj'], $data['sender_id'], $data['receiver_id']]);

        jsonResponse(["message" => "Poruka poslata.", "id" => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("UPDATE poruke SET procitano = TRUE WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Poruka nije pronađena."], 404);
        jsonResponse(["message" => "Poruka označena kao pročitana."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM poruke WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Poruka nije pronađena."], 404);
        jsonResponse(["message" => "Poruka obrisana."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
