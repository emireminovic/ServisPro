<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT z.*, u.tip AS uredjaj_tip, u.serijski_broj,
                       k.naziv_firme AS klijent_naziv,
                       s.naziv_firme AS serviser_naziv
                FROM servisni_zahtevi z
                JOIN uredjaji u ON z.uredjaj_id = u.id
                JOIN users k ON u.klijent_id = k.id
                LEFT JOIN users s ON z.serviser_id = s.id
                WHERE z.id = ?
            ");
            $stmt->execute([$id]);
            $zahtev = $stmt->fetch();
            if (!$zahtev) jsonResponse(["error" => "Zahtev nije pronađen."], 404);
            jsonResponse($zahtev);
        } else {
            $status = $_GET['status'] ?? null;
            $serviser_id = $_GET['serviser_id'] ?? null;
            $klijent_id = $_GET['klijent_id'] ?? null;

            $sql = "SELECT z.*, u.tip AS uredjaj_tip, u.serijski_broj,
                           k.naziv_firme AS klijent_naziv,
                           s.naziv_firme AS serviser_naziv
                    FROM servisni_zahtevi z
                    JOIN uredjaji u ON z.uredjaj_id = u.id
                    JOIN users k ON u.klijent_id = k.id
                    LEFT JOIN users s ON z.serviser_id = s.id";
            $params = [];
            $where = [];

            if ($status) { $where[] = "z.status = ?"; $params[] = $status; }
            if ($serviser_id) { $where[] = "z.serviser_id = ?"; $params[] = $serviser_id; }
            if ($klijent_id) { $where[] = "u.klijent_id = ?"; $params[] = $klijent_id; }

            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY z.datum_prijave DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['opis_kvara', 'uredjaj_id']);

        if (isset($data['opis_kvara']) && strlen($data['opis_kvara']) > 255) {
            $errors[] = "Opis kvara ne sme biti duži od 255 karaktera.";
        }
        if (isset($data['opis_kvara']) && strlen(trim($data['opis_kvara'])) < 5) {
            $errors[] = "Opis kvara mora imati najmanje 5 karaktera.";
        }
        if (isset($data['uredjaj_id']) && $data['uredjaj_id'] <= 0) {
            $errors[] = "Uređaj ID mora biti pozitivan broj.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $check = $db->prepare("SELECT id FROM uredjaji WHERE id = ?");
        $check->execute([$data['uredjaj_id']]);
        if (!$check->fetch()) jsonResponse(["error" => "Uređaj sa tim ID-om ne postoji."], 404);

        $stmt = $db->prepare("INSERT INTO servisni_zahtevi (opis_kvara, fotografija, datum_prijave, status, trajanje_min, uredjaj_id, serviser_id) VALUES (?, ?, CURDATE(), 'prijavljeno', 0, ?, ?)");
        $stmt->execute([
            $data['opis_kvara'],
            $data['fotografija'] ?? null,
            $data['uredjaj_id'],
            $data['serviser_id'] ?? null
        ]);

        $newId = $db->lastInsertId();

        
        $uredjaj = $db->prepare("SELECT klijent_id FROM uredjaji WHERE id = ?");
        $uredjaj->execute([$data['uredjaj_id']]);
        $u = $uredjaj->fetch();
        if ($u) {
            $notif = $db->prepare("INSERT INTO obavestenja (sadrzaj, user_id) VALUES (?, ?)");
            $notif->execute(["Vaš servisni zahtev #{$newId} je kreiran.", $u['klijent_id']]);
        }

        jsonResponse(["message" => "Servisni zahtev kreiran.", "id" => $newId], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $data = getJsonInput();
        $fields = [];
        $params = [];

        if (isset($data['opis_kvara'])) { $fields[] = "opis_kvara = ?"; $params[] = $data['opis_kvara']; }
        if (isset($data['fotografija'])) { $fields[] = "fotografija = ?"; $params[] = $data['fotografija']; }
        if (isset($data['status'])) {
            $validStatuses = ['prijavljeno', 'u radu', 'rešeno', 'fakturisano'];
            if (!in_array($data['status'], $validStatuses)) {
                jsonResponse(["error" => "Nevažeći status."], 400);
            }
            $fields[] = "status = ?"; $params[] = $data['status'];
        }
        if (isset($data['trajanje_min'])) {
            if ($data['trajanje_min'] < 0) jsonResponse(["error" => "Trajanje ne može biti negativno."], 400);
            $fields[] = "trajanje_min = ?"; $params[] = $data['trajanje_min'];
        }
        if (isset($data['datum_dolaska'])) { $fields[] = "datum_dolaska = ?"; $params[] = $data['datum_dolaska']; }
        if (array_key_exists('serviser_id', $data)) { $fields[] = "serviser_id = ?"; $params[] = $data['serviser_id']; }

        if (empty($fields)) jsonResponse(["error" => "Nema podataka."], 400);

        $params[] = $id;
        $stmt = $db->prepare("UPDATE servisni_zahtevi SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Zahtev nije pronađen."], 404);

        
        if (isset($data['status'])) {
            $zahtev = $db->prepare("SELECT z.id, u.klijent_id FROM servisni_zahtevi z JOIN uredjaji u ON z.uredjaj_id = u.id WHERE z.id = ?");
            $zahtev->execute([$id]);
            $z = $zahtev->fetch();
            if ($z) {
                $statusText = ['u radu' => 'prihvaćen i dodeljen serviseru', 'rešeno' => 'označen kao rešen', 'fakturisano' => 'fakturisan'];
                if (isset($statusText[$data['status']])) {
                    $notif = $db->prepare("INSERT INTO obavestenja (sadrzaj, user_id) VALUES (?, ?)");
                    $notif->execute(["Vaš servisni zahtev #{$id} je {$statusText[$data['status']]}.", $z['klijent_id']]);
                }
            }
        }

        jsonResponse(["message" => "Zahtev ažuriran."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM servisni_zahtevi WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Zahtev nije pronađen."], 404);
        jsonResponse(["message" => "Zahtev obrisan."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
