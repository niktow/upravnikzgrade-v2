# Plan prepisivanja: Laravel API + React JS (PWA)

## 1. Cilj prepisivanja
Postojeća aplikacija (Laravel + admin tema) je spora i teško skalabilna za moderan UX. Cilj je potpuni prelazak na:
- **Backend**: Laravel kao čisti API (REST)
- **Frontend**: React JS (Vite)
- **Styling**: Tailwind CSS
- **UI biblioteka (preporuka)**: **shadcn/ui** (Radix UI + Tailwind)
- **PWA**: instalabilna web aplikacija (offline fallback + cache strategije)

Napomena za izbor UI biblioteke:
- `shadcn/ui` je trenutno najbolji izbor za AI-assisted development jer je komponentni kod lokalno u projektu (nije "black-box"), lako se prilagodjava i odlicno radi uz Tailwind.

## 2. Ciljna arhitektura

### Backend (Laravel API)
- Laravel ostaje izvor poslovne logike i pristupa bazi.
- Izbaciti server-rendered blade flow za glavni korisnicki deo.
- Uvesti verzionisan API: `/api/v1/...`
- Standardizovan JSON odgovor i error format.
- Auth preko Laravel Sanctum (SPA/API token pristup prema potrebi).

### Frontend (React JS)
- React JS + Vite.
- React Router za SPA rutiranje.
- React Query (TanStack Query) za data fetching/caching.
- Tailwind + shadcn/ui za dizajn sistem.
- Form validacija: React Hook Form + Zod.
- i18n po potrebi (sr-Latn, sr-Cyrl, en).

### PWA sloj
- `manifest.webmanifest`
- Service Worker (preko `vite-plugin-pwa` ili Workbox pristupa)
- Offline fallback stranica
- Cache strategije za statiku i API GET zahteve

## 3. Preduslovi i tehnicke odluke
Pre implementacije usvojiti:
1. API standard:
   - default paginacija: 20 po strani
   - ucitavanje listi: lazy load
   - sortiranje: nije globalni zahtev; po potrebi na pojedinim ekranima (`asc`/`desc` po broju ili datumu)
   - filtriranje: global search na tabelama je dovoljno za V1
2. Auth model:
   - administratori (internal panel)
   - stanari/vlasnici (tenant portal)
3. Multi-tenant pravila pristupa podacima (po zgradi/ulazu/jedinici):
   - implementirati kao osnovu za buducu komercijalizaciju (SaaS model)
4. Strategija migracije:
   - big-bang prelazak (odjednom), bez postepenog rollout-a
5. Observability:
   - u V1 bez posebnog error tracking/metrics sistema
   - ostaviti osnovne Laravel logove

### 3.1 Dodatno sto treba ustanoviti pre starta
1. Hosting model za produkciju (jedan server ili odvojeno API i frontend).
2. Domeni/subdomeni (`api.domen.rs`, `app.domen.rs`) zbog auth/cookie politike.
3. Nacin prijave stanara: email + lozinka ili jednokratni kod (OTP).
4. Pravila pristupa dokumentima (ko vidi koje PDF-ove i izvestaje).
5. Strategija importa postojecih podataka i minimalni plan povratka (rollback) na staro stanje samo za hitne slucajeve.
6. Minimalni backup plan baze i fajlova pre produkcionog prelaska.

## 4. Faze migracije (korak po korak)

## Faza 0: Analiza i mapiranje (1-2 nedelje)
1. Inventarisati sve postojece module:
   - vlasnici/jedinice
   - troskovi
   - bankovne transakcije
   - izvestaji/uplatnice
   - dokumenti
2. Napraviti tabelu "Postojeci ekran -> Novi API endpoint -> Novi React ekran".
3. Oznaciti kriticne tokove (obracun, uvoz izvoda, izvestaji) kao prioritet P0.
4. Definisati SLA/performance ciljeve (npr. TTFB, API p95, vreme ucitavanja dashboard-a).

## Faza 1: Priprema Laravel API osnove (1 nedelja)
1. Uvesti API versioning (`/api/v1`).
2. Kreirati `ApiResponse` standard (success/error shape).
3. Uvesti `FormRequest` validacije na sve write endpoint-e.
4. Uvesti API Resources za serializaciju modela.
5. Uvesti centralizovan exception handling za API.
6. Dodati OpenAPI/Swagger dokumentaciju endpoint-a.

## Faza 2: Auth i autorizacija (1 nedelja)
1. Sanctum konfiguracija za SPA/API.
2. Login/logout/refresh tokovi.
3. Role/permission politika (admin, manager, owner/tenant).
4. Rate limiting i security hardening:
   - throttling
   - CORS pravilna konfiguracija
   - secure headers

## Faza 3: React foundation (1 nedelja)
1. Kreirati `../frontend/` React JS projekat (Vite) na nivou workspace root-a.
2. Uvesti Tailwind + shadcn/ui.
3. Postaviti osnovni design system:
   - boje, tipografija, spacing tokeni
   - reusable layout komponente
4. Uvesti TanStack Query, Axios/fetch client, auth guard sloj.
5. Uvesti global error boundary i loading skeleton pattern.

## Faza 4: Migracija modula (iterativno, 4-8 nedelja)
Redosled migracije (preporuka):
1. **Autentikacija i korisnicki profil**
2. **Jedinice/vlasnici i listing/filtering**
3. **Troskovi i kategorije**
4. **Transakcije i uskladjivanje uplata**
5. **Izvestaji i dokumenti**

Za svaki modul uraditi isti sablon:
1. Definisati API ugovor (request/response).
2. Implementirati endpoint + testove u Laravelu.
3. Implementirati React stranice i komponente.
4. Uporedno testiranje (stari ekran vs novi ekran).
5. Validirati modul na staging okruzenju i pripremiti za finalni cutover.

## Faza 5: PWA implementacija (1 nedelja)
1. Dodati `manifest.webmanifest` (name, icons, display, theme color).
2. Uvesti service worker registraciju.
3. Definisati cache strategije:
   - static assets: cache-first
   - API GET: stale-while-revalidate
4. Dodati offline fallback (`/offline`).
5. Testirati instalaciju na Android/desktop browserima.

## Faza 6: Performanse i hardening (1-2 nedelje)
1. Backend optimizacija:
   - N+1 eliminacija (eager loading)
   - indeksi u bazi
   - query profiling
2. Frontend optimizacija:
   - code splitting
   - image optimizacija
   - memoization gde je potrebno
3. Uvesti load/performance testove (kriticni endpoint-i).
4. Uvesti monitoring i alerting (API greske, response time, JS greske).

## Faza 7: Cutover i gasenje starog UI-a (1 nedelja)
1. Freeze novih funkcija na starom UI-u.
2. Finalna migracija svih ruta na React aplikaciju (jednokratni prelazak).
3. Smoke + regression test.
4. Plan rollback-a ako kriticni tok padne.
5. Gasenje Blade/admin tema ekrana nakon potvrde stabilnosti.

## 5. Predlog strukture repozitorijuma
Aktuelna struktura (potvrdjeno):
- Laravel backend je u folderu `api/`
- React aplikacija ide u sibling folder `frontend/`

Primer:
- `/api/app`, `/api/routes`, `/api/database`, ... (Laravel API)
- `/frontend` (React JS + Vite + Tailwind + shadcn/ui + PWA)

## 6. Test strategija
1. Backend:
   - Feature testovi za API endpoint-e
   - Permission testovi
   - Contract testovi (JSON shape)
2. Frontend:
   - Component testovi za kljucne UI delove
   - E2E tokovi (login, pregled stanja, placanja, dokumenta)
3. Pre-release:
   - regresija kriticnih finansijskih tokova

## 7. DevOps i okruzenja
1. Odvojeni env var setovi za backend i frontend.
2. CORS + cookie/session konfiguracija po okruzenju.
3. Deploy model:
   - rucni deploy direktno na server (backend + frontend build)
   - frontend build artefakti servirani preko web servera (Nginx/Apache)
4. Pre-deploy checklist (lokalno):
   - backend testovi
   - frontend lint/test
5. CDN opciono za static assets i PWA ikonice.

### 7.1 Operativna checklista za rucni deploy
1. Backup pre deploy-a:
   - baza (dump)
   - `api/storage/app` i `api/.env`
2. Upload novog koda na server (nova release verzija).
3. Laravel koraci na serveru (iz foldera `api/`):
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan migrate --force`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`
4. Frontend koraci (iz foldera `frontend/`):
   - instalacija paketa (`npm install`) ako je potrebno
   - lokalno build React aplikacije (`npm run build`)
   - upload `dist` artefakata na server static lokaciju
   - proveriti da web server vraca `index.html` za SPA rute
5. Posle deploy-a (smoke):
   - login admin naloga
   - otvaranje 2-3 kljucne tabele
   - kreiranje i izmena jednog zapisa
   - provera jednog finansijskog izvestaja
6. Ako deploy nije stabilan u prvih 15-30 min:
   - vratiti prethodni release
   - vratiti prethodni frontend build
   - po potrebi restore baze iz backup-a

### 7.2 Nginx/Apache mapa ruta (api + frontend)
Preporuka domena:
- `app.domen.rs` -> React SPA (`frontend/dist`)
- `api.domen.rs` -> Laravel API (`api/public`)

Nginx primer (sa odvojenim domenima):
- `server_name app.domen.rs;`
- `root /var/www/upravnik/frontend/dist;`
- `location / { try_files $uri /index.html; }`
- `server_name api.domen.rs;`
- `root /var/www/upravnik/api/public;`
- `location / { try_files $uri $uri/ /index.php?$query_string; }`
- `location ~ \.php$ { include fastcgi_params; ... }`

Apache smernice:
- VirtualHost za `app.domen.rs` pokazuje na `frontend/dist`
- U SPA hostu ukljuciti rewrite ka `index.html` kada fajl/direktorijum ne postoji
- VirtualHost za `api.domen.rs` pokazuje na `api/public`
- U API hostu zadrzati standardni Laravel rewrite ka `index.php`

Ako morate jedan domen (fallback opcija):
- `/api/*` proslediti na Laravel (`api/public`)
- sve ostalo servirati iz React `frontend/dist` uz SPA fallback na `index.html`

## 8. Rizici i mitigacija
1. Rizik: Neuskaladjenost starog i novog obracuna.
   - Mitigacija: detaljno staging testiranje na reprezentativnom uzorku podataka pre produkcionog prelaska.
2. Rizik: Auth/cookie problemi izmedju domena.
   - Mitigacija: isti parent domen i precizna Sanctum/CORS konfiguracija.
3. Rizik: Spori endpoint-i pri vecim zgradama.
   - Mitigacija: indeksiranje, cache sloj, async jobs.
4. Rizik: PWA cache vraca zastarele podatke.
   - Mitigacija: verzionisanje cache-a i invalidation pravila.

## 9. Predlog inicijalnog backlog-a (prvih 30 dana)
1. API standard + auth + korisnici
2. React foundation + design system
3. Modul vlasnici/jedinice (end-to-end)
4. Modul troskovi (end-to-end)
5. PWA osnove (manifest + install + offline)

## 10. Definition of Done (za svaki migrirani modul)
1. Endpoint dokumentovan i testiran.
2. UI ekran implementiran u React aplikaciji.
3. Permission pravila proverena.
4. Performance ciljevi zadovoljeni.
5. Modul validiran na staging okruzenju i spreman za produkcioni cutover.

---

## Kratka preporuka tehnologija
- **Frontend**: React JS (Vite)
- **Routing**: react-router-dom
- **State/Data**: TanStack Query
- **Forms/Validation**: React Hook Form + Zod
- **UI**: shadcn/ui + Radix + Tailwind
- **Charts**: Recharts
- **PWA**: vite-plugin-pwa (ili Workbox za napredniji custom setup)

Ovim planom dobijate kontrolisanu migraciju sa jednokratnim (big-bang) prelaskom i jasnim checkpoint-ima za kvalitet i performanse.