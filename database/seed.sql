-- Salvamont Zărnești — Registru digital de activități ale voluntarilor
-- Script de inițializare: tipuri de activități implicite.
-- Rulează acest script (după schema.sql) prin phpMyAdmin.
-- NU conține conturi de utilizator — primul cont de administrator se creează
-- prin pagina de configurare inițială a aplicației (?route=setup), vezi INSTALLATION_CPANEL.md.

INSERT INTO `activity_types` (`name`, `description`, `is_active`) VALUES
('Intervenție', 'Intervenții de salvare montană', 1),
('Patrulare', 'Patrulare în zona montană', 1),
('Atelier', 'Ateliere organizate de Salvamont', 1),
('Curs / Instruire', 'Cursuri și instruiri pentru voluntari', 1),
('Activitate administrativă', 'Activități administrative Salvamont', 1),
('Altă activitate', 'Alte activități organizate de Salvamont', 1)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);
