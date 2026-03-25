# Instalacija Imagick ekstenzije za Laragon (Windows)

## Problem
QR kodovi se ne prikazuju na PDF-u jer PNG generisanje zahteva Imagick ekstenziju.

## Rešenje

### Opcija 1: Brza instalacija preko Laragona (preporučeno)

1. Otvori Laragon Menu → PHP → Quick Settings → Extension
2. Potraži `imagick` u listi
3. Ako postoji, uključi ga i restartuj Apache

### Opcija 2: Ručna instalacija

1. **Preuzmi Imagick DLL za PHP 8.1**:
   - Idi na: https://windows.php.net/downloads/pecl/releases/imagick/
   - Ili: https://pecl.php.net/package/imagick
   - Preuzmi verziju za PHP 8.1 Thread Safe (TS) x64
   - Primer: `php_imagick-3.7.0-8.1-ts-vs16-x64.zip`

2. **Instaliraj fajlove**:
   - Raspakiraj arhivu
   - Kopiraj `php_imagick.dll` u `C:\laragon\bin\php\php-8.1.33\ext\`
   - Kopiraj sve `.dll` fajlove iz `bin\` foldera (ImageMagick DLLs) u `C:\laragon\bin\php\php-8.1.33\`

3. **Aktiviraj ekstenziju**:
   - Otvori `C:\laragon\bin\php\php-8.1.33\php.ini`
   - Dodaj liniju: `extension=imagick`
   - Sačuvaj fajl

4. **Restartuj Apache**:
   - U Laragonu klikni Stop All → Start All

5. **Proveri instalaciju**:
   ```powershell
   php -m | Select-String -Pattern "imagick"
   ```
   Treba da vidiš `imagick` u listi.

## Nakon instalacije

Kada je Imagick instaliran, QR kod će automatski biti generisan kao PNG umesto SVG, što radi savršeno sa DomPDF-om (kao u tvom stickers primeru).

## Test

Posle instalacije, generiši PDF ponovo - QR kod treba da se vidi!
