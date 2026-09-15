-- Migrare: adaugă tipurile de cont „Salvator Montan Atestat" (salvator) și
-- „Formator" (formator) la coloana role. Aceste roluri au aceleași drepturi
-- ca aspirantul (applicant) în aplicație.
-- Rulați o singură dată în phpMyAdmin, pe baza de date a aplicației.
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'applicant', 'salvator', 'formator') NOT NULL DEFAULT 'applicant';
