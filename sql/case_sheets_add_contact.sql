-- Migrare: adaugă contactul victimei (ex. telefon) la fișa de caz.
-- Rulați o singură dată în phpMyAdmin DOAR dacă tabelul case_sheets exista deja.
ALTER TABLE case_sheets
    ADD COLUMN victima_contact VARCHAR(150) NOT NULL DEFAULT '' AFTER victima_stare;
