-- Tabel pentru victimele suplimentare ale unei fișe de caz (evenimente cu
-- victime multiple). Prima victimă rămâne în tabelul case_sheets; aici se
-- salvează victimele adăugate dinamic din formular.
-- Rulați o singură dată în phpMyAdmin, pe baza de date a aplicației.
CREATE TABLE IF NOT EXISTS case_sheet_victims (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_sheet_id INT UNSIGNED NOT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    nume VARCHAR(200) NOT NULL DEFAULT '',
    varsta VARCHAR(20) NOT NULL DEFAULT '',
    sex VARCHAR(5) NOT NULL DEFAULT '',
    judet VARCHAR(100) NOT NULL DEFAULT '',
    tara VARCHAR(100) NOT NULL DEFAULT '',
    stare VARCHAR(255) NOT NULL DEFAULT '',
    contact VARCHAR(150) NOT NULL DEFAULT '',
    localizare_afectiune VARCHAR(255) NOT NULL DEFAULT '',
    finalitate_caz VARCHAR(255) NOT NULL DEFAULT '',
    KEY idx_case_sheet_id (case_sheet_id),
    CONSTRAINT fk_case_sheet_victims_sheet FOREIGN KEY (case_sheet_id) REFERENCES case_sheets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
