-- Adaugă tipul de activitate „Intervenție Salvamont”, folosit la generarea
-- automată a activităților din fișa de caz. Idempotent: nu creează duplicate.
-- Rulați o singură dată în phpMyAdmin, pe baza de date a aplicației.
INSERT INTO activity_types (name, is_active)
SELECT 'Intervenție Salvamont', 1
WHERE NOT EXISTS (
    SELECT 1 FROM activity_types WHERE name = 'Intervenție Salvamont'
);
