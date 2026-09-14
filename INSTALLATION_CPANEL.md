# Instalare pe hosting cPanel — Registru Salvamont Zărnești

Acest ghid este scris pentru o persoană care are acces la cPanel, dar nu este
neapărat programator. Nu este nevoie de acces SSH, Composer sau Node.js —
toate operațiile se fac din cPanel și phpMyAdmin.

---

## 1. Versiunea de PHP necesară

Aplicația necesită **PHP 8.2 sau mai nou** (funcționează și pe 8.1 dacă 8.2
nu este disponibil, dar recomandăm cea mai recentă versiune stabilă oferită
de hosting).

**Cum se setează în cPanel:**

1. Intrați în cPanel → secțiunea **Software** → **Select PHP Version** (sau
   **MultiPHP Manager**).
2. Alegeți domeniul/subdomeniul aplicației și selectați versiunea **8.2**
   (sau cea mai apropiată disponibilă, minim 8.1).
3. Verificați (la **Select PHP Version** → **Extensions**) că sunt bifate:
   `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `session`, `json`. Acestea sunt
   de regulă activate implicit.
4. Salvați.

---

## 2. Crearea bazei de date MySQL/MariaDB în cPanel

1. cPanel → **Databases** → **MySQL® Databases**.
2. La **Create New Database**, introduceți un nume, ex. `registru`
   (va deveni ceva de genul `cpanelusername_registru`). Apăsați **Create
   Database**.
3. Rețineți numele complet al bazei de date afișat (cu prefixul contului).

## 3. Crearea utilizatorului bazei de date

1. Tot în **MySQL® Databases**, secțiunea **MySQL Users** → **Add New User**.
2. Introduceți un nume de utilizator (ex. `registru_app`) și o **parolă
   puternică** (folosiți butonul „Password Generator” și salvați-o într-un
   loc sigur — va fi nevoie de ea la pasul 6).
3. Apăsați **Create User**.

## 4. Atribuirea utilizatorului la baza de date (privilegii)

1. În aceeași pagină, secțiunea **Add User To Database**.
2. Selectați utilizatorul creat la pasul 3 și baza de date creată la pasul 2.
3. Apăsați **Add**.
4. Pe ecranul de privilegii, bifați **ALL PRIVILEGES** și apăsați **Make
   Changes**.

## 5. Importarea schemei bazei de date prin phpMyAdmin

1. cPanel → **Databases** → **phpMyAdmin**.
2. În partea stângă, selectați baza de date creată (`cpanelusername_registru`).
3. Sus, deschideți tab-ul **Import**.
4. La **File to import**, alegeți fișierul `database/schema.sql` din arhiva
   aplicației și apăsați **Go**. Așteptați confirmarea că importul a reușit
   (se vor crea tabelele: `users`, `activity_types`, `activities`,
   `activity_approvals`, `audit_log`, `login_attempts`).
5. Repetați pasul 3-4, de această dată importând `database/seed.sql`
   (adaugă tipurile implicite de activități: Intervenție, Patrulare, Atelier,
   Curs/Instruire, Activitate administrativă, Altă activitate).
6. Verificați în tab-ul **Structure** că toate cele 6 tabele există.

---

## 6. Configurarea fișierului `config.php`

Aplicația citește configurația (baza de date, adresa aplicației, sigla) dintr-un
fișier `config.php` aflat **direct la rădăcina proiectului, în afara
`public_html`** (niciodată în `public/`).

1. În arhiva aplicației, copiați fișierul `config.example.php` și
   redenumiți-l în `config.php` (același folder, rădăcina proiectului).
2. Deschideți-l cu un editor de text simplu (Notepad, TextEdit — nu Word) și
   completați:

   ```php
   <?php
   return [
       'app' => [
           'name' => 'Salvamont Zărnești',
           'base_url' => '/registru/public',
           'logo_path' => '/assets/img/logo/salvamont-placeholder.svg',
       ],
       'db' => [
           'host' => 'localhost',
           'database' => 'cpanelusername_registru',
           'username' => 'cpanelusername_registru',
           'password' => 'parola-generata-la-pasul-3',
           'charset' => 'utf8mb4',
       ],
       'roles' => [
           'applicant',
           'admin',
       ],
   ];
   ```

   - `db.host` este aproape întotdeauna `localhost` pe hosting shared.
   - `db.database` / `db.username` conțin prefixul contului cPanel (ex.
     `cpanelusername_registru`), nu doar numele scurt.
   - `app.base_url` trebuie să reflecte calea reală din browser către folderul
     `public/` al aplicației (vezi pasul 9), ex. `/registru/public` dacă site-ul
     este la `https://domeniultau.ro/registru/public/...`, sau gol (`''`) dacă
     aplicația este chiar la rădăcina domeniului.
   - `app.logo_path` este calea relativă la `app.base_url` unde se află sigla
     (implicit `public/assets/img/logo/salvamont-placeholder.svg`).
   - **Nu ștergeți/redenumiți cheia `roles`** — este folosită de aplicație
     pentru validarea rolurilor la crearea/editarea conturilor.

3. **Nu urcați niciodată `config.php` în locuri publice** (de exemplu în
   interiorul `public/`) și nu îl distribuiți prin email necriptat — conține
   parola bazei de date. Fișierul este deja exclus din control de versiuni
   prin `.gitignore`.

## 7. Configurarea conexiunii la baza de date

Nu este nevoie de alt fișier — conexiunea este citită automat din `config.php`
(secțiunea `db`). Este suficient ca pașii 2-6 de mai sus să fie corecți.

Dacă întâmpinați eroarea „Eroare de conectare la baza de date”, verificați:
- `db.database` / `db.username` conțin prefixul contului cPanel (ex.
  `cpanelusername_registru`), nu doar numele scurt.
- Parola din `config.php` este exact cea setată la pasul 3 (fără spații în plus).
- Utilizatorul are privilegii pe baza de date (pasul 4).

---

## 8. Încărcarea fișierelor aplicației în File Manager

**Recomandat: încărcați proiectul într-un folder din afara `public_html`**
(de exemplu `~/registru_app`), iar publicul va vedea doar folderul `public/`
din interior (vezi pasul 9). Astfel codul sursă, `config.php` și baza de date SQL
nu sunt niciodată accesibile direct din browser.

1. Arhivați local întregul proiect într-un fișier `.zip`.
2. cPanel → **Files** → **File Manager**.
3. Navigați la locația dorită (ex. rădăcina contului, un nivel deasupra
   `public_html`) și creați un folder, ex. `registru_app`.
4. Intrați în folder, **Upload** → alegeți arhiva `.zip`.
5. După încărcare, selectați arhiva → click dreapta → **Extract**.
6. Verificați că structura rezultată conține direct `app/`, `public/`,
   `config.php`, `database/`, `autoload.php` etc. (nu un folder
   intermediar suplimentar — dacă arhiva a creat un folder în plus, mutați
   conținutul un nivel mai sus).

## 9. Configurarea document root-ului domeniului/subdomeniului

Aplicația trebuie servită astfel încât **document root-ul** să fie folderul
`public/` din proiect (niciodată rădăcina proiectului).

### Varianta A — domeniu propriu / subdomeniu dedicat
1. cPanel → **Domains** (sau **Subdomains**).
2. La domeniul/subdomeniul dorit, editați **Document Root** și indicați-l
   spre `registru_app/public` (calea completă către folderul `public/` din
   proiect).
3. Salvați. Domeniul va afișa acum direct aplicația.

### Varianta B — subfolder pe domeniul principal (ex. `domeniu.ro/registru`)
Dacă hosting-ul nu permite schimbarea document root-ului pentru un subfolder,
sau preferați o cale de tip `domeniu.ro/registru`:

1. În `public_html`, creați un folder `registru`.
2. Copiați **conținutul** folderului `public/` din proiect (nu folderul
   `public/` în sine) direct în `public_html/registru/`.
3. Restul proiectului (`app/`, `config.php`, `database/`, `autoload.php`)
   rămâne în afara `public_html`, la același nivel ca înainte (ex.
   `~/registru_app/`).
4. Deoarece `public/index.php` face referire la restul proiectului printr-o
   cale relativă de tipul `dirname(__DIR__) . '/autoload.php'`, dacă mutați
   doar conținutul lui `public/` în `public_html/registru`, trebuie să
   ajustați această cale din `index.php`:
   - `require dirname(__DIR__) . '/autoload.php';`

   astfel încât `dirname(__DIR__)` să indice corect spre `~/registru_app`
   (folderul care conține `autoload.php` și `config.php`). Dacă structura de
   foldere e păstrată identică (`registru_app/public/...` copiat ca atare,
   doar link-uit/simlink-uit ca `public_html/registru`), nu e nevoie de nicio
   modificare. **Cel mai simplu și sigur este să evitați copierea separată și
   să folosiți un symlink**: în File Manager sau prin cerere către
   furnizorul de hosting, creați un link simbolic `public_html/registru` →
   `~/registru_app/public`. Dacă nu aveți posibilitatea de a crea symlink-uri
   din cPanel, solicitați acest lucru suportului tehnic al furnizorului de
   hosting (este o operație obișnuită, fără nevoie de SSH din partea
   dumneavoastră).
5. Actualizați `app.base_url` din `config.php` cu calea reală, ex.
   `/registru/public`.

---

## 10. Permisiuni fișiere și directoare

Din **File Manager**, selectați fișierele/folderele și **Change Permissions**:

- Foldere: `755`
- Fișiere PHP: `644`
- Fișierul `config.php`: `600` (sau `640`, dacă serverul rulează PHP prin alt
  utilizator) — trebuie să fie citibil doar de contul aplicației.
- Folderul `storage/logs/`: `755` (folderul trebuie să fie scriabil de PHP;
  dacă aplicația nu poate scrie erori acolo, treceți temporar la `775` doar
  cât timp diagnosticați, apoi reveniți la `755`).

---

## 11. Activarea HTTPS/SSL

1. cPanel → **Security** → **SSL/TLS Status** (sau **AutoSSL**).
2. Selectați domeniul/subdomeniul și apăsați **Run AutoSSL** (majoritatea
   hosting-urilor oferă certificat Let's Encrypt gratuit automat).
3. După emitere, cPanel → **Domains** → activați opțiunea **Force HTTPS
   Redirect** pentru domeniul respectiv, dacă este disponibilă.
4. Verificați accesând `https://domeniultau.ro/...` — trebuie să apară lacătul
   de securitate în browser.

---

## 12. Crearea primului cont de administrator

1. Accesați în browser: `https://domeniultau.ro/registru/?route=setup`
   (sau adresa echivalentă, în funcție de varianta aleasă la pasul 9).
2. Completați prenume, nume, email, utilizator și o parolă de minim 10
   caractere.
3. Apăsați **Creează contul de administrator**.
4. Această pagină se dezactivează automat imediat ce există un administrator
   în baza de date — la o nouă accesare va afișa un mesaj că inițializarea a
   fost deja făcută.

---

## 13. Testarea autentificării și a fluxului de aprobare

1. Autentificați-vă cu contul de administrator creat la pasul 12.
2. Mergeți la **Voluntari → Cont nou** și creați un cont de test cu rol
   „Aspirant”.
3. Deschideți o fereastră de navigare privată (incognito), autentificați-vă
   cu contul de aspirant și introduceți o activitate (**Adaugă activitate**).
4. Reveniți la contul de administrator → **Activități** → verificați că noua
   activitate apare cu status „În așteptare” → apăsați **Aprobă** sau
   **Respinge** (cu motiv).
5. Reveniți la contul de aspirant → verificați în **Activitățile mele** că
   statusul s-a actualizat (și motivul respingerii, dacă e cazul).

---

## 14. Configurarea backup-ului bazei de date

**Backup manual (recomandat lunar/săptămânal, în plus față de cel automat):**

1. cPanel → **Files** → **Backup** (sau **Backup Wizard**).
2. Secțiunea **Download a MySQL Database Backup** → selectați baza de date
   `cpanelusername_registru` → se descarcă un fișier `.sql.gz`.
3. Păstrați aceste fișiere într-un loc sigur (calculator local, cloud
   storage privat).

**Backup automat oferit de hosting:**

- Majoritatea hosting-urilor cPanel oferă backup automat zilnic/săptămânal
  (cPanel → **Files** → **Backup Wizard**, sau verificați cu furnizorul de
  hosting ce politică de backup au și pentru câte zile păstrează copiile).
- Recomandăm activarea/verificarea acestei opțiuni și, suplimentar, un backup
  manual periodic descărcat local, conform pașilor de mai sus.

**Restaurare din backup:**

1. phpMyAdmin → selectați baza de date → tab **Import** → alegeți fișierul
   `.sql` de backup (dezarhivat, dacă e `.gz`) → **Go**.
2. Dacă restaurați și fișierele aplicației, reîncărcați arhiva din backup în
   File Manager, la aceeași cale ca la pasul 8.

---

## 15. Depanarea erorilor uzuale de instalare

| Simptom | Cauză probabilă | Soluție |
|---|---|---|
| „Eroare de conectare la baza de date” | Date greșite în `config.php` | Verificați `db.database`/`db.username` (cu prefixul contului), `db.password`, `db.host=localhost` |
| Pagină albă (fără mesaj) | `debug` lipsește/e `false` în `config.php`, ascunde eroarea | Adăugați temporar `'debug' => true` în secțiunea `app` din `config.php`, reîncărcați pagina, apoi reveniți la `false` după depanare |
| Eroare 500 la orice pagină | Versiune PHP prea veche sau extensie lipsă | Verificați PHP 8.1+ activ și extensiile `pdo_mysql`, `mbstring` (pasul 1) |
| „View-ul ... nu a fost găsit” | Arhivă extrasă incomplet / folder lipsă | Reîncărcați arhiva completă, verificați că `app/views/` există cu toate subfolderele |
| 404 la orice `?route=...` | Document root greșit | Document root-ul trebuie să fie folderul `public/`, nu rădăcina proiectului (pasul 9) |
| Sigla nu apare | Fișierul placeholder a fost șters fără înlocuire, sau `app.logo_path` din `config.php` nu se potrivește | Adăugați fișierul la calea indicată de `app.logo_path` din `config.php` |
| „Configurarea inițială a fost deja finalizată” la `?route=setup` | Există deja un cont admin | Folosiți login-ul normal; dacă parola s-a pierdut, resetați direct din phpMyAdmin (vezi mai jos) |
| CSS-ul (culorile Salvamont) nu se încarcă | Blocaj CDN Bootstrap sau cache browser | Verificați conexiunea la internet a serverului/browserului; forțați reîncărcare completă (Ctrl+F5) |

**Resetarea manuală a parolei unui administrator (dacă nu mai există alt admin activ), direct din phpMyAdmin:**
Această operație necesită să rulați PHP o singură dată pentru a genera un
hash de parolă (bcrypt) — nu introduceți parola în clar în baza de date.
Cea mai simplă variantă, fără SSH: creați temporar un fișier `hash.php` cu
conținutul de mai jos, încărcați-l în `public/`, accesați-l o singură dată în
browser pentru a obține hash-ul, apoi **ștergeți imediat fișierul**:

```php
<?php echo password_hash('parola-noua-aleasa', PASSWORD_DEFAULT);
```

Copiați rezultatul afișat și actualizați manual coloana `password_hash` a
utilizatorului dorit din tabela `users`, folosind phpMyAdmin (tab **Edit**
pe rândul respectiv). Ștergeți apoi `hash.php` de pe server.

---

## Actualizarea aplicației

1. Faceți backup complet (fișiere + bază de date) conform pasului 14.
2. Descărcați/pregătiți noua versiune a codului sursă.
3. Comparați `database/schema.sql` din noua versiune cu structura curentă;
   dacă au fost adăugate coloane/tabele noi, rulați prin phpMyAdmin doar
   comenzile SQL noi (nu reimportați schema completă peste o bază de date cu
   date existente, pentru a nu pierde informații).
4. Încărcați fișierele noi în File Manager, suprascriind folderele `app/`,
   `public/assets` etc., **păstrând neschimbat fișierul `config.php`** și sigla
   încărcată în `public/assets/img/logo/`.
5. Testați autentificarea și fluxul de aprobare conform pasului 13.
