# Plan razvoja aplikacije za upravnika zgrade (Laravel)

## 1. Tehnička osnova
- **Framework**: Laravel 9 
- **Baza podataka**: MySQL 8 (InnoDB, UTF8MB4)
- **File storage**: Laravel Filesystem (lokalno + opcija S3 kompatibilnih servisa)
- **Scheduling & Jobs**: Laravel Scheduler + Queue za automatizaciju uvoza izvoda i generisanje izveštaja i uplatnica
- **API integracije**: HTTP klijent za IPS QR generator i bankovne API-je

### MVP fokus
- Verzija 1 cilja isključivo na administratorski panel; nema portala za stanare, anketa, SMS/email notifikacija niti automatskih reminder-a.
- Zapisnici i dokumenti se uploaduju ručno kao fajlovi; nema online editora, e-potpisivanja ili objava direktno sa portala.
- Podsistemi poput kućnih saveta, oglasne table i naprednih workflow-ova biće planirani, ali implementacija se odlaže za fazu 2.
- Prioritet: evidencija stanova/vlasnika, troškova, uplata, AIK importa i generisanje mesečnih izveštaja + uplatnica preko DomPDF-a.

## 2. Modul evidencije stanova i vlasnika
- Modeli: `Owner`, `Unit` (tip: Stan/Lokal), pivot `owner_unit` sa meta podacima (datum posedovanja, procenat učešća)
- Polja: broj stana/lokala, površina, broj članova, status aktivan/neaktivan
- Kontakt info vlasnika (telefon, email, adresa)
- Administracija obaveza: definisati po vlasniku/po jedinici šta plaća (komponente troškova)

## 3. Evidencija troškova
- Model `Expense`: tip (ugovoreni mesečni / jednokratni), komponenta troška, opis, dokumenti, status odobreno
- Model `Contract`: povezan sa dobavljačem i troškovima, definiše period, iznos, interval plaćanja
- Planiraj kategorije troškova (održavanje, komunalije, osiguranje, rezervni fond...)

## 4. Evidencija uplata i stanja računa
- Model `BankAccount` + `BankTransaction`
- Automatizacija uvoza izvoda (CSV/MT940) uz mapiranje kolona -> `BankTransaction`
- Praćenje trenutnog stanja: sumirati početno stanje + transakcije, validirati sa bankovnim izvodom
- Veza transakcije sa vlasnikom/jedinicom i odgovarajućim troškom

### 4.1 AIK Banka – integracija i uvoz izvoda
- Komunikacioni kanali: prioritet MT940/ISO20022 fajlovi sa AIK e-banking portala (ručni upload), zatim automatizacija preko SFTP/REST API-ja (AIK Digital Corporate API) kada postanu dostupni
- Scheduler `bank:aik-import` svakog jutra preuzima nove izvode (SFTP) ili čeka ručni upload kroz admin UI; nakon obrade generiše audit log + notifikaciju
- Parser servis (`AikStatementImporter`) mapira kolone: datum valute, šifra transakcije, opis, referenca plaćanja, poziv na broj, iznos, smer (D/K) → `BankTransaction`
- Pravila usklađivanja: pokušaj automatskog linkovanja transakcije sa `Unit`/`Owner` preko poziva na broj ili reference iz IPS QR koda; fallback ručno vezivanje
- Bezbednost: API kredencijali i SFTP ključevi u `.env`  Store; validacija potpisa/hash-a fajla pre obrade; evidencija neuspelih importova radi ponovnog pokušaja
- Reconciliation dashboard prikazuje razlike između bankovnog stanja i internog salda, uz akcije „prihvati“, „odbij“ ili „poveži sa uplatom"

## 5. IPS QR uplatnice
- Servis sloj koji generiše payload za https://ips.nbs.rs/... generator (ili lokalno formiranje QR-a po NBS specifikaciji)
- UI: forma za izbor vlasnika/jedinice i dugovanja, generisanje PDF/PNG uplatnice sa QR-om

## 6. Mesečni izveštaji
- Scheduler job krajem meseca: sabira troškove, uplata, stanje računa
- PDF/Excel izvozi: pregled po kategorijama troškova, prikaz duga po vlasniku, hronološki tok računa
- Arhiva izveštaja

### 6.1 Automatizovano generisanje uplatnica i izveštaja (DomPDF)
- Paket: `barryvdh/laravel-dompdf` za renderovanje jedinstvenog A4 dokumenta (gornji deo finansijski izveštaj, donji deo uplatnica poput pruženog primera)
- Scheduler komanda svakog 1. u mesecu (npr. `billing:generate-statements`) kreira PDF za svakog vlasnika i opcioni zbirni PDF za štampu svih uplatnica
- Template elementi: zaglavlje sa zgradom/periodom, tabela prethodnih troškova i stanja, donji kupon sa poljima (uplatioc, primalac, šifra plaćanja, model/poziv na broj, iznos) i IPS QR kod
- Svaka uplatnica dobija unikatni reference ID i QR payload koji se veže za konkretan `Unit` + period radi kasnijeg automatskog usklađivanja uplata
- Generisani PDF se čuva u `storage/app/statements/{year}/{month}/` + audit zapis o datumu generisanja; omogućiti resend/regen funkcionalnost i slanje vlasnicima emailom

## 7. Ugovori i dokument menadžment
- Upload dokumenata (PDF, slike) preko `Document` modela; tagovanje (ugovor, račun, zapisnik...)
- Povezivanje dokumenata sa vlasnicima, troškovima, transakcijama
- Mini preglednik slika/dokumenata u aplikaciji

## 8. Administrativni panel
> Sekcije 9 i 10 predstavljaju backlog za fazu 2; MVP uključuje samo osnovnu administraciju iz sekcija 2–8.

## 9. Kućni saveti (zbor stanara)
- Evidencija prisustva i glasanja: check-in lista po stanu/lokalu,.
- Zapisniku treba dodati opciju uplodovanje dokumenta zapisnika i odluka.



## 10. Dodatne preporuke
1. **Notifikacije**: email/sms obaveštenja vlasnicima o dugu ili važnim obaveštenjima (Laravel Notifications)
2. **Workflow odobravanja**: troškovi i ugovori prolaze kroz status (predlog -> odobren -> arhiviran)
3. **Import vlasnika**: CSV import postojećeg stanja da bi se ubrzala inicijalna migracija
4. **API sloj**: REST ili GraphQL za potencijalne mobilne klijente
5. **Testiranje**: Pest ili PHPUnit + Feature testovi za ključne procese (obračun troškova, import izvoda)
6. **Monitoring i logovanje**: Laravel Telescope za debug, Log channels za bankovne integracije
7. **Bezbednost**: rate limiting za API, šifrovanje osetljivih podataka (npr. bankovni podaci)

## 11. Sledeći koraci
- Definisati user stories i UI wireframe-ove
- Postaviti inicijalni Laravel projekat, autentikaciju i osnovne modele/migracije
- Implementirati modul evidencije vlasnika i jedinica kao temelj za ostale funkcionalnosti
