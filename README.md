# Registru digital de activități — Salvamont Zărnești

Aplicație web pentru înregistrarea și aprobarea activităților voluntarilor
(aspiranți) Salvamont Zărnești: intervenții, patrulare, ateliere, cursuri,
activități administrative etc. Conturile sunt create exclusiv de administrator
(fără înregistrare publică).

## Tehnologie

- **Backend:** PHP 8.2+ nativ, arhitectură MVC simplă (fără framework), scris
  special pentru găzduire shared cu cPanel (fără SSH, Composer sau Node.js).
- **Bază de date:** MySQL / MariaDB, acces exclusiv prin PDO cu prepared
  statements.
- **Frontend:** Bootstrap 5 (încărcat din CDN) + CSS propriu cu identitatea
  vizuală Salvamont (roșu/portocaliu montan, fără albastru).
- **Autentificare:** sesiuni PHP native, `password_hash()`/`password_verify()`,
  protecție CSRF pe toate formularele, protecție brute-force la autentificare.

## Structura proiectului

```
.
├── app/
│   ├── Controllers/     Controllere (Auth, Setup, Applicant, Admin)
│   ├── Core/            Router, autentificare, CSRF, conexiune DB, helpers
│   ├── Models/          Acces la date (User, Activity, ActivityType, AuditLog)
│   └── views/           Template-uri PHP (layouts, auth, applicant, admin)
├── autoload.php         Autoloader PSR-4 minimal, fără Composer
├── config/config.php    Configurare centrală, citește .env
├── database/
│   ├── schema.sql       Schema completă a bazei de date
│   └── seed.sql         Tipuri de activități implicite
├── public/              Document root — configurați hosting-ul spre acest folder
│   ├── index.php        Front controller (singurul punct de intrare)
│   └── assets/          CSS, imagini (inclusiv sigla Salvamont)
├── storage/logs/        Fișiere scrise de aplicație (nu trebuie expuse public)
├── .env.example         Șablon pentru configurarea bazei de date/aplicației
└── INSTALLATION_CPANEL.md   Ghid pas-cu-pas de instalare în cPanel
```

## Roluri

- **Aspirant** — introduce activități proprii, vede statusul (În așteptare /
  Aprobată / Respinsă) și motivul respingerii, editează activitățile aflate
  încă în așteptare, vede un sumar propriu (ore aprobate, statistici).
- **Administrator** — creează/dezactivează/reactivează conturi, resetează
  parole, vede și filtrează toate activitățile, aprobă sau respinge (cu motiv),
  corectează activități, generează rapoarte și exportă CSV.

Un voluntar nu își poate aproba/respinge propria activitate, chiar dacă are
și rol de administrator.

## Instalare

Vezi [INSTALLATION_CPANEL.md](INSTALLATION_CPANEL.md) pentru pașii compleți
de instalare pe un hosting cPanel (bază de date, import schemă, configurare
`.env`, permisiuni, SSL, primul cont de administrator etc.).

Pe scurt, pentru dezvoltare/testare locală cu un server PHP + MySQL:

1. Creați o bază de date și importați `database/schema.sql`, apoi
   `database/seed.sql`.
2. Copiați `.env.example` ca `.env` la rădăcina proiectului și completați
   datele de conectare la baza de date.
3. Configurați serverul web astfel încât document root-ul să fie folderul
   `public/`.
4. Accesați `?route=setup` (ex: `http://localhost/index.php?route=setup`)
   pentru a crea primul cont de administrator. Această pagină se dezactivează
   automat după crearea primului administrator.
5. Autentificați-vă și creați conturi pentru aspiranți din
   `Voluntari → Cont nou`.

## Configurarea siglei și a cromaticii

- **Sigla**: înlocuiți fișierul placeholder
  `public/assets/img/logo-salvamont.svg` cu sigla oficială Salvamont Zărnești,
  păstrând exact același nume de fișier (sau actualizați referințele din
  `app/views/layouts/main.php` și `app/views/auth/*.php` dacă folosiți alt
  format, de exemplu `.png`).
- **Cromatica**: culorile aplicației (roșu/portocaliu, fără albastru) sunt
  definite central în `public/assets/css/style.css`, în blocul `:root`
  (variabilele `--sv-primary`, `--sv-accent` etc.). Modificați acolo pentru a
  ajusta paleta.

## Securitate implementată

- Parole hash-uite cu `password_hash()` (bcrypt), niciodată stocate în clar.
- Interogări parametrizate (PDO prepared statements) — fără concatenare de
  input în SQL.
- Protecție CSRF pe toate formularele (token de sesiune verificat la fiecare
  POST).
- Escapare HTML consecventă (`htmlspecialchars`) pentru orice date afișate.
- Sesiuni cu cookie `HttpOnly`, `SameSite=Lax` și `Secure` automat pe HTTPS.
- Control strict al accesului pe rol (`require_role()`), fără acces direct la
  paginile administrative.
- Protecție simplă la brute-force pe autentificare (blocare temporară după 5
  încercări eșuate).
- Jurnal de audit (`audit_log`) pentru acțiuni administrative (creare/editare
  cont, dezactivare/reactivare, resetare parolă, aprobare/respingere).
- Fără date sensibile în codul sursă — toate secretele vin din `.env`
  (fișier exclus din control de versiuni prin `.gitignore`).

## Date de test

Nu sunt incluse conturi demo cu parole implicite (evită riscul de securitate
al unor credențiale implicite uitate în producție). Creați primul
administrator prin `?route=setup`, apoi creați conturi de test din interfața
de administrare.

## Backup, restaurare și actualizare

Vezi secțiunile dedicate din [INSTALLATION_CPANEL.md](INSTALLATION_CPANEL.md).
