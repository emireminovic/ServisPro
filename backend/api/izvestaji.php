<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

$db = (new Database())->getConnection();
$type = $_GET['type'] ?? '';

switch ($type) {

    
    case 'kvarovi_po_tipu':
        $stmt = $db->query("
            SELECT u.tip, COUNT(z.id) AS broj_kvarova
            FROM servisni_zahtevi z
            JOIN uredjaji u ON z.uredjaj_id = u.id
            GROUP BY u.tip
            ORDER BY broj_kvarova DESC
        ");
        jsonResponse($stmt->fetchAll());
        break;

    
    case 'kvarovi_po_mesecu':
        $stmt = $db->query("
            SELECT DATE_FORMAT(datum_prijave, '%Y-%m') AS mesec, COUNT(*) AS broj
            FROM servisni_zahtevi
            GROUP BY mesec
            ORDER BY mesec DESC
            LIMIT 12
        ");
        jsonResponse($stmt->fetchAll());
        break;


    case 'efikasnost_servisera':
        $stmt = $db->query("
            SELECT s.id, s.naziv_firme,
                   COUNT(z.id) AS broj_intervencija,
                   ROUND(AVG(CASE WHEN z.trajanje_min > 0 THEN z.trajanje_min END), 1) AS prosecno_trajanje,
                   SUM(CASE WHEN z.status = 'rešeno' OR z.status = 'fakturisano' THEN 1 ELSE 0 END) AS reseno
            FROM users s
            LEFT JOIN servisni_zahtevi z ON z.serviser_id = s.id
            WHERE s.uloga = 'serviser'
            GROUP BY s.id, s.naziv_firme
            ORDER BY broj_intervencija DESC
        ");
        jsonResponse($stmt->fetchAll());
        break;

    
    case 'najcesci_kvarovi':
        $stmt = $db->query("
            SELECT opis_kvara, COUNT(*) AS pojavljivanja
            FROM servisni_zahtevi
            GROUP BY opis_kvara
            ORDER BY pojavljivanja DESC
            LIMIT 10
        ");
        jsonResponse($stmt->fetchAll());
        break;

    
    case 'finansije':
        $stmt = $db->query("
            SELECT
                COUNT(*) AS ukupno_faktura,
                SUM(iznos) AS ukupan_prihod,
                SUM(CASE WHEN status_naplate = 'plaćeno' THEN iznos ELSE 0 END) AS naplaceno,
                SUM(CASE WHEN status_naplate = 'neplaćeno' THEN iznos ELSE 0 END) AS nenaplaceno,
                SUM(materijal) AS ukupan_materijal,
                SUM(putni_troskovi) AS ukupni_putni,
                SUM(radni_sati) AS ukupni_sati
            FROM fakture
        ");
        jsonResponse($stmt->fetch());
        break;

    
    case 'prihod_po_klijentu':
        $stmt = $db->query("
            SELECT k.naziv_firme, SUM(f.iznos) AS ukupno, COUNT(f.id) AS broj_faktura
            FROM fakture f
            JOIN servisni_zahtevi z ON f.zahtev_id = z.id
            JOIN uredjaji u ON z.uredjaj_id = u.id
            JOIN users k ON u.klijent_id = k.id
            GROUP BY k.id, k.naziv_firme
            ORDER BY ukupno DESC
        ");
        jsonResponse($stmt->fetchAll());
        break;

    
    case 'prihod_po_serviseru':
        $stmt = $db->query("
            SELECT s.naziv_firme, SUM(f.iznos) AS ukupno, COUNT(f.id) AS broj_faktura
            FROM fakture f
            JOIN servisni_zahtevi z ON f.zahtev_id = z.id
            JOIN users s ON z.serviser_id = s.id
            GROUP BY s.id, s.naziv_firme
            ORDER BY ukupno DESC
        ");
        jsonResponse($stmt->fetchAll());
        break;

    
    case 'dashboard':
        $stats = [];

        $stmt = $db->query("SELECT COUNT(*) AS val FROM uredjaji");
        $stats['ukupno_uredjaja'] = $stmt->fetch()['val'];

        $stmt = $db->query("SELECT COUNT(*) AS val FROM servisni_zahtevi WHERE status IN ('prijavljeno','u radu')");
        $stats['otvoreni_zahtevi'] = $stmt->fetch()['val'];

        $stmt = $db->query("SELECT COUNT(*) AS val FROM fakture WHERE status_naplate = 'neplaćeno'");
        $stats['neplacene_fakture'] = $stmt->fetch()['val'];

        $stmt = $db->query("SELECT COALESCE(SUM(iznos),0) AS val FROM fakture WHERE status_naplate = 'neplaćeno'");
        $stats['neplaceni_iznos'] = $stmt->fetch()['val'];

        $stmt = $db->query("SELECT COUNT(*) AS val FROM licence WHERE DATEDIFF(datum_isteka, CURDATE()) <= 30");
        $stats['licence_isticu'] = $stmt->fetch()['val'];

        $stmt = $db->query("SELECT COUNT(*) AS val FROM servisni_zahtevi");
        $stats['ukupno_zahteva'] = $stmt->fetch()['val'];

        jsonResponse($stats);
        break;

    default:
        jsonResponse(["error" => "Nepoznat tip izveštaja. Dozvoljeno: kvarovi_po_tipu, kvarovi_po_mesecu, efikasnost_servisera, najcesci_kvarovi, finansije, prihod_po_klijentu, prihod_po_serviseru, dashboard"], 400);
}
