# OPTIMIZACIJA PERFORMANSI - Upravnik Zgrade

## Problemi
- Projekat je mali ali radi sporo
- Filament admin panel može biti spor

## Rešenja

### 1. **Cache konfiguracije** (prvo uradi ovo)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. **Optimizuj Composer autoload**
```bash
composer install --optimize-autoloader --no-dev
# ili ako trebaš dev pakete:
composer dump-autoload -o
```

### 3. **Proveri .env fajl**
Postavi:
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

### 4. **Opcache (PHP)**
U `php.ini` proveri da li je uključeno:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 5. **Database optimizacija**
```bash
# Dodaj indekse na često pretraživane kolone
php artisan migrate
```

### 6. **Filament specifične optimizacije**

U `.env` dodaj:
```env
FILAMENT_CACHE_ENABLED=true
```

### 7. **Eager loading**
Proveri da uvek koristiš `with()` pri učitavanju relacija.

### 8. **Isključi nepotrebne service provider-e**
U `config/app.php` zakomentariši nekorišćene provider-e.

### 9. **Ukloni nepotrebne middleware**

### 10. **Query optimization**
Koristi `debugbar` da vidiš koliko upita se izvršava:
```bash
composer require barryvdh/laravel-debugbar --dev
```

## Brzo testiranje
```bash
# Očisti sve
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Zatim keširaj
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizuj autoload
composer dump-autoload -o
```

## Da li je do teme?
Filament tema može biti malo sporija zbog Livewire-a koji šalje mnogo zahteva. 
Ali uz gornje optimizacije trebalo bi biti brže!
