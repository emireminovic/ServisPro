<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        $serviser_id = $_GET['serviser_id'] ?? null;

        $sql = "SELECT g.*, s.naziv_firme AS serviser_naziv
                FROM gps_podaci g
                JOIN users s ON g.serviser_id = s.id";
        $params = [];

        if ($serviser_id) {
            $sql .= " WHERE g.serviser_id = ?";
            $params[] = $serviser_id;
        }
        $sql .= " ORDER BY g.vreme DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll());
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['lokacija', 'serviser_id']);

        if (isset($data['lokacija']) && strlen($data['lokacija']) > 50) {
            $errors[] = "Lokacija ne sme biti duža od 50 karaktera.";
        }
        if (isset($data['kilometraza']) && $data['kilometraza'] < 0) {
            $errors[] = "Kilometraža ne može biti negativna.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $km = $data['kilometraza'] ?? 0;
        $stmt = $db->prepare("INSERT INTO gps_podaci (lokacija, vreme, kilometraza, serviser_id) VALUES (?, NOW(), ?, ?)");
        $stmt->execute([$data['lokacija'], $km, $data['serviser_id']]);

        jsonResponse(["message" => "GPS zapis dodat.", "id" => $db->lastInsertId()], 201);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM gps_podaci WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "GPS zapis nije pronađen."], 404);
        jsonResponse(["message" => "GPS zapis obrisan."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
