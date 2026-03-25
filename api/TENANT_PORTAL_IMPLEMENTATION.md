# Plan Implementacije Stanarskog Portala

## 📋 Pregled

Jednostavan sistem sa dva tipa korisnika (Upravnik/Stanar), oglasnom tablom i online pristupom za stanare.

**Principi:**
- ✅ Jednostavnost pre svega
- ✅ Stanari samo čitaju podatke
- ✅ Bez email notifikacija (za sada)
- ✅ Fokus na prikaz informacija

---

## 🏗️ Arhitektura

```
┌─────────────────────────────────────────────────────────────┐
│                      Aplikacija                             │
├─────────────────────────────────────────────────────────────┤
│  /admin/*                    │  /portal/*                   │
│  Filament Admin Panel        │  Stanarski Portal            │
│  (Upravnici)                 │  (Livewire/Blade)            │
│  - Sve upravljačke funkcije  │  - Uvid u stanje             │
│  - Obračuni                  │  - Oglasna tabla             │
│  - Oglasna tabla (CRUD)      │  - Troškovi (read-only)      │
│  - Izveštaji                 │  - PDF računi                │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Plan Implementacije po Fazama

### FAZA 1: Baza Podataka i Modeli (1-2 dana)

#### 1.1 Proširenje User Modela

```php
// Dodati u users tabelu
'role' => enum('admin', 'manager', 'tenant'), // admin = super admin, manager = upravnik, tenant = stanar
'owner_id' => nullable, foreign key -> owners // Veza sa Owner za stanare
```

#### 1.2 Nova Migracija: Oglasna Tabla

```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->text('content');
    $table->string('priority')->default('normal'); // low, normal, high, urgent
    $table->string('type')->default('info'); // info, warning, maintenance, meeting, financial
    $table->boolean('is_pinned')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### 1.3 Nova Migracija: Mesečni Obračuni

```php
Schema::create('unit_billing_statements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
    $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
    $table->string('period'); // Format: 2026-01
    $table->decimal('opening_balance', 14, 2)->default(0); // Početni saldo
    $table->decimal('charges', 14, 2)->default(0); // Zaduženja
    $table->decimal('payments', 14, 2)->default(0); // Uplate
    $table->decimal('closing_balance', 14, 2)->default(0); // Krajnji saldo
    $table->date('generated_at');
    $table->string('pdf_path')->nullable();
    $table->timestamps();
    
    $table->unique(['unit_id', 'period']);
});
```

#### 1.4 Ažuriranje User Modela

```php
// User.php - dodati relacije i helper metode
public function owner(): BelongsTo
{
    return $this->belongsTo(Owner::class);
}

public function isManager(): bool
{
    return in_array($this->role, ['admin', 'manager']);
}

public function isTenant(): bool
{
    return $this->role === 'tenant';
}
```

---

### FAZA 2: Stanarski Portal (2-3 dana)

#### 2.1 Struktura Ruta

```php
// routes/web.php
Route::prefix('portal')->name('portal.')->middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/stanje', [PortalController::class, 'balance'])->name('balance');
    Route::get('/troskovi', [PortalController::class, 'expenses'])->name('expenses');
    Route::get('/racuni', [PortalController::class, 'statements'])->name('statements');
    Route::get('/racuni/{statement}/download', [PortalController::class, 'downloadStatement'])->name('statements.download');
    Route::get('/oglasi', [PortalController::class, 'announcements'])->name('announcements');
});
```

#### 2.2 Layout Stanarskog Portala

```
┌──────────────────────────────────────────────────────────┐
│  🏠 Stanarski Portal          [Ime Stanara] [Odjava]      │
├──────────────────────────────────────────────────────────┤
│ ┌─────────┐                                               │
│ │ Meni    │   ┌──────────────────────────────────────┐   │
│ │         │   │                                      │   │
│ │ Početna │   │         GLAVNI SADRŽAJ               │   │
│ │ Stanje  │   │                                      │   │
│ │ Troškovi│   │   - Saldo kartica                    │   │
│ │ Računi  │   │   - Lista troškova                   │   │
│ │ Oglasi  │   │   - Mesečni izveštaji                │   │
│ │         │   │                                      │   │
│ └─────────┘   └──────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

#### 2.3 Stranice Portala

| Stranica | Sadržaj |
|----------|---------|
| Dashboard | Saldo + poslednji oglasi + brzi pregled |
| Stanje | Detaljan prikaz zaduženja i uplata |
| Troškovi | Lista troškova zgrade sa kategorijama |
| Računi | Mesečni izvodi sa mogućnošću download PDF |
| Oglasi | Oglasna tabla |

---

### FAZA 3: Admin Panel Proširenja (1-2 dana)

#### 3.1 Filament Resurs: Oglasi

```php
// app/Filament/Resources/AnnouncementResource.php
// CRUD za oglase - naslov, sadržaj, prioritet, tip
```

#### 3.2 Kreiranje Stanarskih Naloga

Dodati akciju u OwnerResource za brzo kreiranje korisničkog naloga:
- Generiši nalog sa email-om vlasnika
- Postavi privremenu lozinku
- Poveži sa Owner modelom

#### 3.3 Stranica za Mesečni Obračun

```php
// app/Filament/Pages/GenerateBillingStatements.php
// Izbor perioda i stambene zajednice
// Generisanje obračuna za sve stanove
// Download svih PDF-ova odjednom
```

---

## 📁 Struktura Fajlova

```
app/
├── Http/
│   ├── Controllers/
│   │   └── PortalController.php
│   └── Middleware/
│       └── EnsureTenant.php
├── Models/
│   ├── Announcement.php (NOVO)
│   └── UnitBillingStatement.php (NOVO)
├── Services/
│   └── BalanceCalculator.php (NOVO)
└── Filament/
    ├── Resources/
    │   └── AnnouncementResource.php (NOVO)
    └── Pages/
        └── GenerateBillingStatements.php (NOVO)

resources/views/
└── portal/
    ├── layouts/
    │   └── app.blade.php
    ├── dashboard.blade.php
    ├── balance.blade.php
    ├── expenses.blade.php
    ├── statements.blade.php
    └── announcements.blade.php

database/migrations/
├── XXXX_add_role_and_owner_to_users_table.php
├── XXXX_create_announcements_table.php
└── XXXX_create_unit_billing_statements_table.php
```

---

## 🎯 Prioriteti

| # | Stavka | Opis |
|---|--------|------|
| 1 | User uloge + veza sa Owner | Osnova za pristup |
| 2 | Middleware za portal | Zaštita ruta |
| 3 | Portal layout + dashboard | Osnovni UI |
| 4 | Prikaz stanja/salda | Glavna potreba |
| 5 | Lista troškova | Read-only prikaz |
| 6 | Oglasna tabla | Komunikacija |
| 7 | Mesečni PDF izveštaji | Download računa |

---

## ⚠️ Napomene za Hosting

1. **PHP 8.1+** obavezno
2. **Cron job** za scheduler (ako bude potreban):
   ```
   * * * * * cd /path && php artisan schedule:run
   ```
3. **PDF fontovi** - proveriti ćirilicu (DejaVu Sans)
4. **Storage link**: `php artisan storage:link`

---

## 🕐 Procena Vremena

| Faza | Procena |
|------|---------|
| Faza 1 - Baza i modeli | 1-2 dana |
| Faza 2 - Stanarski portal | 2-3 dana |
| Faza 3 - Admin proširenja | 1-2 dana |
| **UKUPNO** | **4-7 dana** |

---

## ✅ Sledeći Koraci

1. ✅ Plan odobren
2. ✅ Kreirati migracije
3. ✅ Ažurirati User model
4. ✅ Kreirati Announcement i UnitBillingStatement modele
5. ✅ Implementirati portal (Faza 2)
6. ✅ Kreirati Filament resurs za Announcements (Faza 3)
7. ✅ Dodati akciju za kreiranje stanarskog naloga

---

## 🎉 IMPLEMENTACIJA ZAVRŠENA

---

*Dokument kreiran: Februar 2026*
*Verzija: 1.1 - Pojednostavljena verzija*
