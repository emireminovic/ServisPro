<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT l.*, s.naziv_firme AS serviser_naziv,
                       DATEDIFF(l.datum_isteka, CURDATE()) AS dana_do_isteka
                FROM licence l
                JOIN users s ON l.serviser_id = s.id
                WHERE l.id = ?
            ");
            $stmt->execute([$id]);
            $licenca = $stmt->fetch();
            if (!$licenca) jsonResponse(["error" => "Licenca nije pronađena."], 404);
            jsonResponse($licenca);
        } else {
            $serviser_id = $_GET['serviser_id'] ?? null;
            $istice = $_GET['istice'] ?? null; 

            $sql = "SELECT l.*, s.naziv_firme AS serviser_naziv,
                           DATEDIFF(l.datum_isteka, CURDATE()) AS dana_do_isteka
                    FROM licence l
                    JOIN users s ON l.serviser_id = s.id";
            $params = [];
            $where = [];

            if ($serviser_id) { $where[] = "l.serviser_id = ?"; $params[] = $serviser_id; }
            if ($istice) { $where[] = "DATEDIFF(l.datum_isteka, CURDATE()) <= ?"; $params[] = intval($istice); }

            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY l.datum_isteka ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['broj', 'datum_izdavanja', 'datum_isteka', 'proizvodjac', 'serviser_id']);

        if (isset($data['broj']) && strlen($data['broj']) > 30) {
            $errors[] = "Broj licence ne sme biti duži od 30 karaktera.";
        }
        if (isset($data['datum_izdavanja'], $data['datum_isteka'])) {
            if ($data['datum_isteka'] <= $data['datum_izdavanja']) {
                $errors[] = "Datum isteka mora biti posle datuma izdavanja.";
            }
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $check = $db->prepare("SELECT id FROM licence WHERE broj = ?");
        $check->execute([$data['broj']]);
        if ($check->fetch()) jsonResponse(["error" => "Broj licence već postoji."], 409);

        $stmt = $db->prepare("INSERT INTO licence (broj, datum_izdavanja, datum_isteka, proizvodjac, serviser_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['broj'], $data['datum_izdavanja'], $data['datum_isteka'], $data['proizvodjac'], $data['serviser_id']]);

        jsonResponse(["message" => "Licenca kreirana.", "id" => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $data = getJsonInput();
        $fields = [];
        $params = [];

        if (isset($data['broj'])) { $fields[] = "broj = ?"; $params[] = $data['broj']; }
        if (isset($data['datum_izdavanja'])) { $fields[] = "datum_izdavanja = ?"; $params[] = $data['datum_izdavanja']; }
        if (isset($data['datum_isteka'])) { $fields[] = "datum_isteka = ?"; $params[] = $data['datum_isteka']; }
        if (isset($data['proizvodjac'])) { $fields[] = "proizvodjac = ?"; $params[] = $data['proizvodjac']; }
        if (isset($data['serviser_id'])) { $fields[] = "serviser_id = ?"; $params[] = $data['serviser_id']; }

        if (empty($fields)) jsonResponse(["error" => "Nema podataka za ažuriranje."], 400);

        $params[] = $id;
        $stmt = $db->prepare("UPDATE licence SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Licenca nije pronađena."], 404);
        jsonResponse(["message" => "Licenca ažurirana."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM licence WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Licenca nije pronađena."], 404);
        jsonResponse(["message" => "Licenca obrisana."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
