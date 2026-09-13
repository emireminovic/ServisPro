<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT f.*, z.opis_kvara, k.naziv_firme AS klijent_naziv
                FROM fakture f
                JOIN servisni_zahtevi z ON f.zahtev_id = z.id
                JOIN uredjaji u ON z.uredjaj_id = u.id
                JOIN users k ON u.klijent_id = k.id
                WHERE f.id = ?
            ");
            $stmt->execute([$id]);
            $faktura = $stmt->fetch();
            if (!$faktura) jsonResponse(["error" => "Faktura nije pronađena."], 404);
            jsonResponse($faktura);
        } else {
            $status = $_GET['status_naplate'] ?? null;
            $klijent_id = $_GET['klijent_id'] ?? null;

            $sql = "SELECT f.*, z.opis_kvara, k.naziv_firme AS klijent_naziv
                    FROM fakture f
                    JOIN servisni_zahtevi z ON f.zahtev_id = z.id
                    JOIN uredjaji u ON z.uredjaj_id = u.id
                    JOIN users k ON u.klijent_id = k.id";
            $params = [];
            $where = [];
            if ($status) { $where[] = "f.status_naplate = ?"; $params[] = $status; }
            if ($klijent_id) { $where[] = "u.klijent_id = ?"; $params[] = $klijent_id; }
            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY f.datum_izdavanja DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['iznos', 'zahtev_id']);

        if (isset($data['iznos']) && $data['iznos'] <= 0) $errors[] = "Iznos mora biti veći od 0.";
        if (isset($data['materijal']) && $data['materijal'] < 0) $errors[] = "Materijal ne može biti negativan.";
        if (isset($data['putni_troskovi']) && $data['putni_troskovi'] < 0) $errors[] = "Putni troškovi ne mogu biti negativni.";
        if (isset($data['radni_sati']) && $data['radni_sati'] < 0) $errors[] = "Radni sati ne mogu biti negativni.";

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $check = $db->prepare("SELECT id FROM fakture WHERE zahtev_id = ?");
        $check->execute([$data['zahtev_id']]);
        if ($check->fetch()) jsonResponse(["error" => "Faktura za ovaj zahtev već postoji."], 409);

        $stmt = $db->prepare("INSERT INTO fakture (datum_izdavanja, iznos, materijal, putni_troskovi, radni_sati, status_naplate, zahtev_id) VALUES (CURDATE(), ?, ?, ?, ?, 'neplaćeno', ?)");
        $stmt->execute([
            $data['iznos'],
            $data['materijal'] ?? 0,
            $data['putni_troskovi'] ?? 0,
            $data['radni_sati'] ?? 0,
            $data['zahtev_id']
        ]);

        $newId = $db->lastInsertId();

        
        $z = $db->prepare("SELECT u.klijent_id FROM servisni_zahtevi z JOIN uredjaji u ON z.uredjaj_id = u.id WHERE z.id = ?");
        $z->execute([$data['zahtev_id']]);
        $row = $z->fetch();
        if ($row) {
            $notif = $db->prepare("INSERT INTO obavestenja (sadrzaj, user_id) VALUES (?, ?)");
            $notif->execute(["Faktura #{$newId} je kreirana – iznos: " . number_format($data['iznos'], 0, ',', '.') . " RSD.", $row['klijent_id']]);
        }

        jsonResponse(["message" => "Faktura kreirana.", "id" => $newId], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $data = getJsonInput();
        $fields = [];
        $params = [];

        foreach (['iznos','materijal','putni_troskovi','radni_sati','status_naplate'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }

        if (empty($fields)) jsonResponse(["error" => "Nema podataka."], 400);

        $params[] = $id;
        $stmt = $db->prepare("UPDATE fakture SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Faktura nije pronađena."], 404);
        jsonResponse(["message" => "Faktura ažurirana."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM fakture WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Faktura nije pronađena."], 404);
        jsonResponse(["message" => "Faktura obrisana."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
