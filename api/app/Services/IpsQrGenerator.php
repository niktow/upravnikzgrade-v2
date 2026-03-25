<?php

namespace App\Services;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Log;

class IpsQrGenerator
{
    /**
     * Generate IPS QR code payload according to NBS specification
     * 
     * @param array $data Payment data
     * @return string QR code payload
     */
    public function generatePayload(array $data): string
    {
        Log::info('IpsQrGenerator::generatePayload - Input data:', $data);
        
        // Transliteriraj i očisti sve tekstualne podatke
        // N i P polja mogu biti višelinijska
        $recipientName = $this->cleanText($data['recipient_name'] ?? '', true);
        $paymentPurpose = $this->cleanText($data['payment_purpose'] ?? '');
        $payerName = !empty($data['payer_name']) ? $this->cleanText($data['payer_name'], true) : '';
        $payerAddress = !empty($data['payer_address']) ? $this->cleanText($data['payer_address'], true) : '';
        
        // Kombinuj ime i adresu uplatioca za P polje (ako postoje)
        $payerInfo = '';
        if (!empty($payerName)) {
            $payerInfo = $payerName;
            if (!empty($payerAddress)) {
                $payerInfo .= "\n" . $payerAddress;
            }
        }
        
        // Formatiraj račun - ukloni sve karaktere osim cifara
        $recipientAccount = preg_replace('/[^0-9]/', '', $data['recipient_account'] ?? '');
        
        // Formatiraj iznos - zameni tačku sa zarezom i spoji sa valutom
        $amount = $data['amount'] ?? '';
        $currency = $data['currency'] ?? 'RSD';
        $amountFormatted = str_replace('.', ',', $amount);
        $currencyAmount = $currency . $amountFormatted; // RSD2000,00
        
        // Model i poziv na broj - BEZ CRTICE (NBS standard)
        $modelReference = $data['model_reference'] ?? '';
        $modelReference = str_replace('-', '', $modelReference); // Ukloni crticu
        
        $fields = [
            'K' => 'PR',  // Obavezno - konstanta za plaćanje
            'V' => '01',  // Verzija standarda
            'C' => '1',   // Character set (1 = UTF-8)
            'R' => $recipientAccount,                 // Račun primaoca (samo cifre)
            'N' => $recipientName,                    // Ime primaoca (višelinijski, velika slova)
            'I' => $currencyAmount,                   // Valuta + Iznos (RSD2000,00)
        ];
        
        // P je opciono ali jako bitno polje - uplatilac
        if (!empty($payerInfo)) {
            $fields['P'] = $payerInfo;
        }
        
        $fields['SF'] = $data['payment_code'] ?? '189';   // Šifra plaćanja
        $fields['S'] = $paymentPurpose;                   // Svrha plaćanja (velika slova)
        $fields['RO'] = $modelReference;                  // Model i poziv na broj (BEZ CRTICE)

        // Dodaj opcione podatke
        if (!empty($data['payer_account'])) {
            $fields['PR'] = preg_replace('/[^0-9]/', '', $data['payer_account']);
        }
        if (!empty($data['payer_name']) && empty($payerInfo)) {
            // Ako nije postavljeno P, dodaj PN
            $fields['PN'] = $this->cleanText($data['payer_name']);
        }

        // Kreiraj payload prema IPS specifikaciji
        $payload = [];
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                $payload[] = "{$key}:{$value}";
            }
        }

        $result = implode('|', $payload);
        Log::info('IpsQrGenerator::generatePayload - Result: ' . $result);
        
        return $result;
    }

    /**
     * Clean and transliterate text for IPS QR code
     * 
     * @param string $text
     * @param bool $multiline Allow newlines for multi-line fields
     * @return string
     */
    protected function cleanText(string $text, bool $multiline = false): string
    {
        // Transliteriraj tekst
        $text = $this->transliterate($text);
        
        // Normalizuj višestruke razmake u jedan razmak (ali zadrži nove redove ako je multiline)
        if ($multiline) {
            // Zameni višestruke razmake, ali zadrži \n
            $text = preg_replace('/[^\S\n]+/', ' ', $text);
        } else {
            // Ukloni sve novi redovi i normalizuj razmake
            $text = preg_replace('/\s+/', ' ', $text);
        }
        
        // Trim razmake sa početka i kraja
        $text = trim($text);
        
        // Konvertuj u VELIKA SLOVA (NBS standard)
        $text = mb_strtoupper($text, 'UTF-8');
        
        return $text;
    }

    /**
     * Transliterate Serbian Cyrillic to Latin
     * 
     * @param string $text
     * @return string
     */
    protected function transliterate(string $text): string
    {
        $cyrillic = ['а', 'б', 'в', 'г', 'д', 'ђ', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'љ', 'м', 'н', 'њ', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'џ', 'ш', 
                     'А', 'Б', 'В', 'Г', 'Д', 'Ђ', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'Љ', 'М', 'Н', 'Њ', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Џ', 'Ш'];
        $latin = ['a', 'b', 'v', 'g', 'd', 'dj', 'e', 'z', 'z', 'i', 'j', 'k', 'l', 'lj', 'm', 'n', 'nj', 'o', 'p', 'r', 's', 't', 'c', 'u', 'f', 'h', 'c', 'c', 'dz', 's',
                  'A', 'B', 'V', 'G', 'D', 'Dj', 'E', 'Z', 'Z', 'I', 'J', 'K', 'L', 'Lj', 'M', 'N', 'Nj', 'O', 'P', 'R', 'S', 'T', 'C', 'U', 'F', 'H', 'C', 'C', 'Dz', 'S'];
        
        $text = str_replace($cyrillic, $latin, $text);
        
        // Remove special latin characters (č, ć, š, ž, đ)
        $specialChars = ['č', 'ć', 'š', 'ž', 'đ', 'Č', 'Ć', 'Š', 'Ž', 'Đ'];
        $replacements = ['c', 'c', 's', 'z', 'dj', 'C', 'C', 'S', 'Z', 'Dj'];
        
        return str_replace($specialChars, $replacements, $text);
    }

    /**
     * Generate QR code image
     * 
     * @param string $payload IPS QR payload
     * @param int $size QR code size in pixels
     * @return string SVG string
     */
    public function generateQrCode(string $payload, int $size = 200): string
    {
        Log::info('IpsQrGenerator::generateQrCode - Input payload: ' . $payload);
        Log::info('IpsQrGenerator::generateQrCode - Size: ' . $size);
        
        // Transliterate to ASCII-compatible characters
        $payload = $this->transliterate($payload);
        Log::info('IpsQrGenerator::generateQrCode - After transliteration: ' . $payload);
        
        try {
            $renderer = new ImageRenderer(
                new RendererStyle($size, 2),
                new SvgImageBackEnd()
            );
            
            $writer = new Writer($renderer);
            
            $svg = $writer->writeString($payload);
            
            Log::info('IpsQrGenerator::generateQrCode - SVG generated successfully');
            Log::info('IpsQrGenerator::generateQrCode - SVG length: ' . strlen($svg));
            Log::info('IpsQrGenerator::generateQrCode - SVG preview: ' . substr($svg, 0, 300));
            
            return $svg;
        } catch (\Exception $e) {
            Log::error('IpsQrGenerator::generateQrCode - ERROR: ' . $e->getMessage());
            Log::error('IpsQrGenerator::generateQrCode - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Generate QR code as PNG data URI (better for PDF)
     * 
     * @param string $payload IPS QR payload
     * @param int $size QR code size in pixels
     * @return string PNG data URI or SVG fallback
     */
    public function generateQrCodePng(string $payload, int $size = 200): string
    {
        Log::info('IpsQrGenerator::generateQrCodePng - Input payload: ' . $payload);
        Log::info('IpsQrGenerator::generateQrCodePng - Size: ' . $size);
        
        // Transliterate to ASCII-compatible characters
        $payload = $this->transliterate($payload);
        Log::info('IpsQrGenerator::generateQrCodePng - After transliteration: ' . $payload);
        
        // Metoda 1: Pokušaj direktno sa GD kroz custom implementaciju
        try {
            Log::info('IpsQrGenerator::generateQrCodePng - Trying GD direct approach');
            
            // Generiši QR matricu kao niz nula i jedinica
            // Ovo zahteva kompleksniju implementaciju, pa prelazimo na EPS backend
            
            // Pokušaj EPS backend (ne zahteva ekstenzije)
            $renderer = new ImageRenderer(
                new RendererStyle($size, 0),
                new EpsImageBackEnd()
            );
            
            $writer = new Writer($renderer);
            $epsData = $writer->writeString($payload);
            
            Log::info('IpsQrGenerator::generateQrCodePng - EPS generated, length: ' . strlen($epsData));
            
            // EPS se može direktno koristiti, ali DomPDF možda ne podržava
            // Vraćamo SVG kao fallback jer je bolji od EPS za PDF
            Log::info('IpsQrGenerator::generateQrCodePng - Falling back to SVG (better for DomPDF)');
            return $this->generateQrCode($payload, $size);
            
        } catch (\Exception $e) {
            Log::error('IpsQrGenerator::generateQrCodePng - ERROR: ' . $e->getMessage());
            // Fallback to SVG
            return $this->generateQrCode($payload, $size);
        }
    }
    
    /**
     * Generate QR as PNG file and return path
     * 
     * @param string $payload
     * @param string $filePath
     * @param int $size
     * @return string File path
     */
    public function generateQrCodeFile(string $payload, string $filePath, int $size = 200): string
    {
        Log::info('IpsQrGenerator::generateQrCodeFile - Generating to: ' . $filePath);
        
        try {
            // Koristi SimpleSoftwareIO paket ako je dostupan
            if (class_exists('SimpleSoftwareIO\\QrCode\\Facades\\QrCode')) {
                $payload = $this->transliterate($payload);
                \QrCode::format('png')->size($size)->margin(2)->generate($payload, $filePath);
                Log::info('IpsQrGenerator::generateQrCodeFile - File saved with SimpleSoftwareIO');
                return $filePath;
            } else {
                // Fallback: save PNG from data
                $pngDataUri = $this->generateQrCodePng($payload, $size);
                if (str_starts_with($pngDataUri, 'data:image')) {
                    $pngData = base64_decode(substr($pngDataUri, strpos($pngDataUri, ',') + 1));
                    file_put_contents($filePath, $pngData);
                    Log::info('IpsQrGenerator::generateQrCodeFile - File saved from data URI');
                    return $filePath;
                }
                throw new \Exception('Failed to generate PNG data');
            }
        } catch (\Exception $e) {
            Log::error('IpsQrGenerator::generateQrCodeFile - ERROR: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate complete IPS QR code from payment data
     * 
     * @param array $data Payment data
     * @param int $size QR code size in pixels
     * @return string SVG string
     */
    public function generate(array $data, int $size = 200): string
    {
        $payload = $this->generatePayload($data);
        return $this->generateQrCode($payload, $size);
    }

    /**
     * Generate payment reference (model and call number)
     * Format za model 97: 97 + XX (kontrolna cifra) + YYYYYYYY (osnovni broj)
     * Primer: 976026010013 = 97 + 60 + 26010013
     * 
     * @param string $model Model plaćanja (97, 99, etc.)
     * @param string $callNumber Poziv na broj (osnovni broj)
     * @return string Formatted reference
     */
    public function formatReference(string $model, string $callNumber): string
    {
        // Za model 97, kontrolna cifra ide POSLE modela, PRE osnovnog broja
        if ($model === '97') {
            $checkDigit = $this->calculateMod97CheckDigit($callNumber);
            return "{$model}{$checkDigit}{$callNumber}";
        }
        
        return "{$model}{$callNumber}";
    }

    /**
     * Calculate MOD 97 check digit for model 97
     * Formula: osnovni_broj + "00", MOD 97, kontrolna = 98 - remainder
     * 
     * @param string $number Osnovni broj
     * @return string Kontrolna cifra (2 cifre)
     */
    protected function calculateMod97CheckDigit(string $number): string
    {
        // Dodaj "00" na kraj osnovnog broja
        $numberWith00 = $number . '00';
        
        // Izračunaj MOD 97
        $remainder = intval($numberWith00) % 97;
        
        // Kontrolna cifra je 98 - remainder
        $checkDigit = 98 - $remainder;
        
        // Formatiraj na 2 cifre
        $checkDigitFormatted = str_pad($checkDigit, 2, '0', STR_PAD_LEFT);
        
        Log::info("calculateMod97CheckDigit: Base={$number}, Remainder={$remainder}, CheckDigit={$checkDigitFormatted}");
        
        return $checkDigitFormatted;
    }

    /**
     * Generate unique call number for unit and period
     * 
     * Format: GGMMTTBB (8 cifara - MOD 97 će dodati 2 kontrolne cifre)
     * - GG: Godina (2 cifre)
     * - MM: Mesec (2 cifre)
     * - TT: Tip (00 = stan, 01 = lokal)
     * - BB: Broj stana/lokala (2 cifre)
     * 
     * Kontrolna cifra će biti dodata automatski za model 97
     * 
     * Primer: STAN-13 u januaru 2026 → 26010013 (+ MOD 97 check = 2601001398)
     * Primer: LOKAL-5 u januaru 2026 → 26010105
     * 
     * @param int $unitId Unit ID
     * @param string $period Period (e.g., '2026-01')
     * @return string Call number (8 cifara)
     */
    public function generateCallNumber(int $unitId, string $period): string
    {
        // Učitaj jedinicu da bismo dobili tip i identifier
        $unit = \App\Models\Unit::find($unitId);
        
        if (!$unit) {
            throw new \Exception("Unit with ID {$unitId} not found");
        }
        
        $date = \Carbon\Carbon::parse($period . '-01');
        $year = $date->format('y');   // GG (26 za 2026)
        $month = $date->format('m');  // MM (01 za januar)
        
        // Odredi tip: 00 za stan, 01 za lokal
        $type = ($unit->type === 'lokal') ? '01' : '00';
        
        // Izvuci broj iz identifiera (npr. "STAN-13" → 13, "LOKAL-5" → 5)
        preg_match('/\d+/', $unit->identifier, $matches);
        $unitNumber = isset($matches[0]) ? intval($matches[0]) : $unitId;
        
        // Formatiraj broj na 2 cifre (sa vodećom nulom ako je potrebno)
        $unitNumberPadded = str_pad($unitNumber, 2, '0', STR_PAD_LEFT);
        
        // Format: GGMMTTBB (8 cifara - kontrolna cifra se dodaje kasnije)
        $callNumber = "{$year}{$month}{$type}{$unitNumberPadded}";
        
        Log::info("generateCallNumber: Unit ID={$unitId}, Type={$unit->type}, Identifier={$unit->identifier}, Number={$unitNumber}, Result={$callNumber}");
        
        return $callNumber;
    }
}
