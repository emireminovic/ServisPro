CREATE DATABASE  servisni_sistem
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE servisni_sistem;

CREATE TABLE users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  naziv_firme VARCHAR(100)  NOT NULL,
  adresa      VARCHAR(150)  NOT NULL,
  email       VARCHAR(50)   NOT NULL UNIQUE,
  lozinka     VARCHAR(255)  NOT NULL,
  uloga       ENUM('administrator','menadzer','serviser','klijent') NOT NULL DEFAULT 'klijent',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE  uredjaji (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  tip                ENUM('fiskalna kasa','POS','štampač') NOT NULL,
  serijski_broj      VARCHAR(30)  NOT NULL UNIQUE,
  datum_instalacije  DATE         NOT NULL,
  garancija_do       DATE         DEFAULT NULL,
  proizvodjac        VARCHAR(40)  DEFAULT NULL,
  verzija_softvera   VARCHAR(20)  DEFAULT NULL,
  status             ENUM('aktivno','u servisu','neispravno','zamenjeno') NOT NULL DEFAULT 'aktivno',
  klijent_id         INT          NOT NULL,
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (klijent_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE  servisni_zahtevi (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  opis_kvara     VARCHAR(255) NOT NULL,
  fotografija    VARCHAR(255) DEFAULT NULL,
  datum_prijave  DATE         NOT NULL,
  status         ENUM('prijavljeno','u radu','rešeno','fakturisano') NOT NULL DEFAULT 'prijavljeno',
  trajanje_min   INT          NOT NULL DEFAULT 0,
  datum_dolaska  DATETIME     DEFAULT NULL,
  uredjaj_id     INT          NOT NULL,
  serviser_id    INT          DEFAULT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uredjaj_id)  REFERENCES uredjaji(id) ON DELETE CASCADE,
  FOREIGN KEY (serviser_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE  fakture (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  datum_izdavanja  DATE          NOT NULL,
  iznos            DECIMAL(10,2) NOT NULL CHECK (iznos > 0),
  materijal        DECIMAL(10,2) NOT NULL DEFAULT 0,
  putni_troskovi   DECIMAL(10,2) NOT NULL DEFAULT 0,
  radni_sati       DECIMAL(5,2)  NOT NULL DEFAULT 0,
  status_naplate   ENUM('plaćeno','neplaćeno') NOT NULL DEFAULT 'neplaćeno',
  zahtev_id        INT           NOT NULL UNIQUE,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (zahtev_id) REFERENCES servisni_zahtevi(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE  licence (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  broj             VARCHAR(30)  NOT NULL UNIQUE,
  datum_izdavanja  DATE         NOT NULL,
  datum_isteka     DATE         NOT NULL,
  proizvodjac      VARCHAR(40)  NOT NULL,
  serviser_id      INT          NOT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (serviser_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE  poruke (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  sadrzaj        VARCHAR(255) NOT NULL,
  vreme_slanja   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  procitano      BOOLEAN      NOT NULL DEFAULT FALSE,
  sender_id      INT          NOT NULL,
  receiver_id    INT          NOT NULL,
  FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE  obavestenja (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  sadrzaj        VARCHAR(255) NOT NULL,
  vreme_slanja   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  procitano      BOOLEAN      NOT NULL DEFAULT FALSE,
  user_id        INT          NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE  gps_podaci (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  lokacija      VARCHAR(50)    NOT NULL,
  vreme         DATETIME       NOT NULL,
  kilometraza   DECIMAL(10,2)  NOT NULL DEFAULT 0,
  serviser_id   INT            NOT NULL,
  FOREIGN KEY (serviser_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


INSERT INTO users (naziv_firme, adresa, email, lozinka, uloga) VALUES
('Admin Sistem',    'Novi Pazar, 28. Novembra 12',            'admin@servis.rs',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator'),
('Servis Plus',     'Novi Pazar, Stevana Nemanje 5',          'marko@servis.rs',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'serviser'),
('Tehno Servis',    'Kragujevac, Kneza Miloša 44',            'jovan@servis.rs',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'serviser'),
('Market Lav',      'Tutin, Husein-bega Gradaščevića 8',      'lav@market.rs',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'klijent'),
('Maxi Prodavnica', 'Sjenica, Trg Bratstva 1',                'maxi@prodavnica.rs',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'klijent'),
('Restoran Dolina', 'Novi Pazar, Rifata Burdževića 30',       'dolina@resto.rs',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'klijent'),
('Menadžment Tim',  'Novi Pazar, 28. Novembra 12',            'ana@servis.rs',       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'menadzer'),
('Quick Servis',    'Raška, Ibarska 22',                      'dejan@servis.rs',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'serviser');

INSERT INTO uredjaji (tip, serijski_broj, datum_instalacije, garancija_do, proizvodjac, verzija_softvera, status, klijent_id) VALUES
('fiskalna kasa', 'FK-2024-00112',  '2024-03-15', '2026-03-15', 'Galeb Group', 'v3.2.1',  'aktivno',    4),
('POS',           'POS-2024-00455', '2024-05-20', '2026-05-20', 'HCP',         'v2.0.4',  'u servisu',  4),
('štampač',       'STP-2023-01089', '2023-11-01', '2025-11-01', 'INT Raster',  'v1.8.0',  'aktivno',    5),
('fiskalna kasa', 'FK-2024-00287',  '2024-01-10', '2026-01-10', 'Galeb Group', 'v3.1.0',  'neispravno', 5),
('POS',           'POS-2023-00998', '2023-09-05', '2025-09-05', 'Genius',      'v4.1.2',  'aktivno',    6),
('fiskalna kasa', 'FK-2025-00034',  '2025-02-14', '2027-02-14', 'Galeb Group', 'v3.3.0',  'aktivno',    6),
('štampač',       'STP-2024-00671', '2024-07-22', '2026-07-22', 'INT Raster',  'v1.9.1',  'zamenjeno',  4),
('POS',           'POS-2025-00102', '2025-06-01', '2027-06-01', 'HCP',         'v2.1.0',  'aktivno',    5);

INSERT INTO servisni_zahtevi (opis_kvara, fotografija, datum_prijave, status, trajanje_min, datum_dolaska, uredjaj_id, serviser_id) VALUES
('POS terminal ne očitava kartice – kontakt čitač ne reaguje',       NULL, '2026-09-01', 'u radu',       0,  '2026-09-02 09:30:00', 2, 2),
('Fiskalna kasa ne štampa račune – greška na termalnoj glavi',       NULL, '2026-09-03', 'prijavljeno',  0,  NULL,                  4, NULL),
('Štampač ne vuče papir, zaglavljuje se traka',                      NULL, '2026-08-20', 'rešeno',      45,  '2026-08-20 14:00:00', 3, 3),
('Softver se zamrzava pri izdavanju dnevnog izveštaja',              NULL, '2026-08-15', 'fakturisano', 90,  '2026-08-15 10:00:00', 1, 2),
('Ekran POS terminala treperi i gubi dodir',                          NULL, '2026-09-10', 'prijavljeno',  0,  NULL,                  5, NULL),
('Neispravan barcode skener – ne čita QR kodove',                    NULL, '2026-09-08', 'u radu',       0,  '2026-09-08 15:30:00', 6, 8),
('Kasa prikazuje grešku E-04 pri fiskalnom zaključku',               NULL, '2026-08-28', 'rešeno',      60,  '2026-08-28 11:00:00', 1, 2);

INSERT INTO fakture (datum_izdavanja, iznos, materijal, putni_troskovi, radni_sati, status_naplate, zahtev_id) VALUES
('2026-08-16',  8500.00, 3000.00, 1500.00, 4.0, 'plaćeno',   4),
('2026-08-22',  4200.00, 1200.00,  800.00, 2.5, 'neplaćeno', 3),
('2026-09-05', 12000.00, 6000.00, 2000.00, 4.0, 'neplaćeno', 7);

INSERT INTO licence (broj, datum_izdavanja, datum_isteka, proizvodjac, serviser_id) VALUES
('LIC-2024-0045',  '2024-01-15', '2026-01-15', 'Galeb Group', 2),
('LIC-2025-0112',  '2025-03-01', '2027-03-01', 'HCP',         3),
('CERT-2024-0078', '2024-06-10', '2026-06-10', 'INT Raster',  2),
('LIC-2025-0203',  '2025-09-01', '2027-09-01', 'Genius',      8),
('CERT-2026-0011', '2026-02-20', '2026-10-20', 'Galeb Group', 3);

INSERT INTO poruke (sadrzaj, vreme_slanja, procitano, sender_id, receiver_id) VALUES
('Marko, možeš li sutra da odeš do Market Lav? POS ne radi.', '2026-09-10 09:15:00', TRUE,  7, 2),
('Može, krećem ujutru u 8. Javim kad stignem.',               '2026-09-10 09:22:00', TRUE,  2, 7),
('Dejane, imaš novi zahtev za Restoran Dolina.',              '2026-09-08 14:30:00', FALSE, 7, 8),
('Primljeno, idem danas popodne.',                             '2026-09-08 14:45:00', TRUE,  8, 7);

INSERT INTO obavestenja (sadrzaj, vreme_slanja, procitano, user_id) VALUES
('Vaš servisni zahtev #1 je prihvaćen i dodeljen serviseru.',  '2026-09-01 10:00:00', TRUE,  4),
('Licenca LIC-2024-0045 ističe za 30 dana.',                   '2026-09-10 08:00:00', FALSE, 2),
('Faktura #3 je kreirana – iznos: 12.000 RSD.',                '2026-09-05 16:00:00', FALSE, 5),
('Servisni zahtev #3 je označen kao rešen.',                   '2026-08-21 11:30:00', TRUE,  5),
('Sertifikat CERT-2026-0011 ističe 20. oktobra 2026.',         '2026-09-11 08:00:00', FALSE, 3);

INSERT INTO gps_podaci (lokacija, vreme, kilometraza, serviser_id) VALUES
('Tutin, Husein-bega Gradaščevića 8',      '2026-09-10 08:45:00', 32.00, 2),
('Sjenica, Trg Bratstva 1',                '2026-09-09 10:20:00', 58.00, 3),
('Novi Pazar, Rifata Burdževića 30',        '2026-09-08 15:10:00',  5.00, 8),
('Raška, Ibarska 22',                       '2026-09-07 09:00:00',  0.00, 8);
