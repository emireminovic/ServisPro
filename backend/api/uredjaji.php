<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
[$method, $id] = getRequestInfo();

switch ($method) {

    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT u.*, k.naziv_firme AS klijent_naziv
                FROM uredjaji u
                JOIN users k ON u.klijent_id = k.id
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $uredjaj = $stmt->fetch();
            if (!$uredjaj) jsonResponse(["error" => "Uređaj nije pronađen."], 404);
            jsonResponse($uredjaj);
        } else {
            $tip = $_GET['tip'] ?? null;
            $klijent_id = $_GET['klijent_id'] ?? null;

            $sql = "SELECT u.*, k.naziv_firme AS klijent_naziv FROM uredjaji u JOIN users k ON u.klijent_id = k.id";
            $params = [];
            $where = [];

            if ($tip) { $where[] = "u.tip = ?"; $params[] = $tip; }
            if ($klijent_id) { $where[] = "u.klijent_id = ?"; $params[] = $klijent_id; }

            if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
            $sql .= " ORDER BY u.id";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = getJsonInput();
        $errors = validateRequired($data, ['tip', 'serijski_broj', 'datum_instalacije', 'klijent_id']);

        $validTypes = ['fiskalna kasa', 'POS', 'štampač'];
        if (isset($data['tip']) && !in_array($data['tip'], $validTypes)) {
            $errors[] = "Tip mora biti: fiskalna kasa, POS ili štampač.";
        }
        if (isset($data['serijski_broj']) && strlen($data['serijski_broj']) > 30) {
            $errors[] = "Serijski broj ne sme biti duži od 30 karaktera.";
        }
        if (isset($data['datum_instalacije']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['datum_instalacije'])) {
            $errors[] = "Datum instalacije mora biti u formatu YYYY-MM-DD.";
        }
        if (isset($data['klijent_id']) && $data['klijent_id'] <= 0) {
            $errors[] = "Klijent ID mora biti pozitivan broj.";
        }

        if (!empty($errors)) jsonResponse(["errors" => $errors], 400);

        $check = $db->prepare("SELECT id FROM uredjaji WHERE serijski_broj = ?");
        $check->execute([$data['serijski_broj']]);
        if ($check->fetch()) jsonResponse(["error" => "Serijski broj već postoji."], 409);

        $stmt = $db->prepare("INSERT INTO uredjaji (tip, serijski_broj, datum_instalacije, garancija_do, proizvodjac, verzija_softvera, status, klijent_id) VALUES (?, ?, ?, ?, ?, ?, 'aktivno', ?)");
        $stmt->execute([
            $data['tip'], $data['serijski_broj'], $data['datum_instalacije'],
            $data['garancija_do'] ?? null, $data['proizvodjac'] ?? null,
            $data['verzija_softvera'] ?? null, $data['klijent_id']
        ]);

        jsonResponse(["message" => "Uređaj uspešno dodat.", "id" => $db->lastInsertId()], 201);
        break;

    case 'PUT':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $data = getJsonInput();
        $fields = [];
        $params = [];

        foreach (['tip','serijski_broj','datum_instalacije','garancija_do','proizvodjac','verzija_softvera','status','klijent_id'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }

        if (empty($fields)) jsonResponse(["error" => "Nema podataka za ažuriranje."], 400);

        $params[] = $id;
        $stmt = $db->prepare("UPDATE uredjaji SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Uređaj nije pronađen."], 404);
        jsonResponse(["message" => "Uređaj ažuriran."]);
        break;

    case 'DELETE':
        if (!$id) jsonResponse(["error" => "ID je obavezan."], 400);
        $stmt = $db->prepare("DELETE FROM uredjaji WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) jsonResponse(["error" => "Uređaj nije pronađen."], 404);
        jsonResponse(["message" => "Uređaj obrisan."]);
        break;

    default:
        jsonResponse(["error" => "Metoda nije podržana."], 405);
}
