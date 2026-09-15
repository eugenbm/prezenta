-- Migrare: adaugă modul de evacuare și către cine a fost predată victima.
-- Rulați o singură dată în phpMyAdmin DOAR dacă tabelul case_sheets exista deja
-- (dacă îl creați acum din case_sheets.sql, coloanele sunt deja incluse).
ALTER TABLE case_sheets
    ADD COLUMN mod_evacuare VARCHAR(20) NOT NULL DEFAULT '' AFTER transport,
    ADD COLUMN predata_catre VARCHAR(200) NOT NULL DEFAULT '' AFTER mod_evacuare;
