<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $db->prepare("SELECT id, naziv_firme, adresa, email, uloga, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if (!$user) jsonResponse(["error" => "Korisnik nije pronađen."], 404);
            jsonResponse($user);
        } else {
            $uloga = $_GET['uloga'] ?? null;
            if ($uloga) {
                $stmt = $db->prepare("SELECT id, naziv_firme, adresa, email, uloga, created_at FROM users WHERE uloga = ? ORDER BY id");
                $stmt->execute([$uloga]);
            } else {
                $stmt = $db->query("SELECT id, naziv_firme, adresa, email, uloga, created_at FROM users ORDER BY id");
            }
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['naziv_firme', 'adresa', 'email', 'lozinka', 'uloga']);

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email format nije validan.";
        }
        $validRoles = ['administrator', 'menadzer', 'serviser', 'klijent'];
        if (isset($data['uloga']) && !in_array($data['uloga'], $validRoles)) {
            $errors[] = "Uloga mora biti: administrator, menadzer, serviser ili klijent.";
        }
        if (isset($data['naziv_firme']) && strlen($data['naziv_firme']) > 100) {
            $errors[] = "Naziv firme ne sme biti duži od 100 karaktera.";
        }
        if (isset($data['email']) && strlen($data['email']) > 50) {
            $errors[] = "Email ne sme biti duži od 50 karaktera.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) jsonResponse(["error" => "Email već postoji."], 409);

        $stmt = $db->prepare("INSERT INTO users (naziv_firme, adresa, email, lozinka, uloga) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['naziv_firme'], $data['adresa'], $data['email'],
            password_hash($data['lozinka'], PASSWORD_DEFAULT), $data['uloga']
        ]);
        jsonResponse(["message" => "Korisnik uspešno kreiran.", "id" => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $data = getJsonInput();
        $errors = validateRequired($data, ['naziv_firme', 'adresa', 'email', 'uloga']);
        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $stmt = $db->prepare("UPDATE users SET naziv_firme = ?, adresa = ?, email = ?, uloga = ? WHERE id = ?");
        $stmt->execute([$data['naziv_firme'], $data['adresa'], $data['email'], $data['uloga'], $id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Korisnik nije pronađen."], 404);
        jsonResponse(["message" => "Korisnik ažuriran."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Korisnik nije pronađen."], 404);
        jsonResponse(["message" => "Korisnik obrisan."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}