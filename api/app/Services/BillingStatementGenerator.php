<?php

namespace App\Services;

use App\Models\HousingCommunity;
use App\Models\Unit;
use App\Models\Expense;
use App\Models\UnitTypePricing;
use App\Models\UnitLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BillingStatementGenerator
{
    protected IpsQrGenerator $qrGenerator;

    public function __construct(IpsQrGenerator $qrGenerator)
    {
        $this->qrGenerator = $qrGenerator;
    }

    /**
     * Generate monthly billing statement for a unit
     * 
     * @param Unit $unit
     * @param string $period Format: YYYY-MM
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateForUnit(Unit $unit, string $period): \Barryvdh\DomPDF\PDF
    {
        $data = $this->prepareUnitData($unit, $period);
        
        // Kreiraj unos u kartici stana (ledger)
        $this->createLedgerEntry($unit, $period, $data['totalAmount']);
        
        return Pdf::loadView('billing.statement', $data)
            ->setPaper('a4')
            ->setOption('defaultFont', 'DejaVu Sans');
    }

    /**
     * Generate statements for all units in a housing community
     * 
     * @param HousingCommunity $community
     * @param string $period Format: YYYY-MM
     * @return array Array of PDFs
     */
    public function generateForCommunity(HousingCommunity $community, string $period): array
    {
        $pdfs = [];
        
        foreach ($community->units()->where('is_active', true)->get() as $unit) {
            $pdfs[$unit->identifier] = $this->generateForUnit($unit, $period);
        }
        
        return $pdfs;
    }

    /**
     * Prepare billing data for a unit
     * 
     * @param Unit $unit
     * @param string $period
     * @return array
     */
    protected function prepareUnitData(Unit $unit, string $period): array
    {
        $periodDate = Carbon::parse($period . '-01')->locale('sr_Latn');
        $community = $unit->housingCommunity;
        
        // Dohvati cenu iz cenovnika za tip jedinice
        $pricing = UnitTypePricing::getPriceForUnit($unit, $periodDate->toDateString());
        $unitFee = $pricing ? $pricing->calculateFeeForUnit($unit) : 0;
        
        // Dohvati dodatne troškove za period (ako ih ima)
        $expenses = Expense::where('housing_community_id', $community->id)
            ->where(function ($query) use ($unit) {
                $query->whereNull('unit_id')
                    ->orWhere('unit_id', $unit->id);
            })
            ->whereYear('due_date', $periodDate->year)
            ->whereMonth('due_date', $periodDate->month)
            ->with('category')
            ->get();

        // Grupiši troškove po kategorijama
        $expensesByCategory = $expenses->groupBy('category.name')->map(function ($items) {
            return [
                'items' => $items,
                'total' => $items->sum('amount'),
            ];
        });

        $additionalExpenses = $expenses->sum('amount');
        
        // Ukupan iznos = cena po tipu + dodatni troškovi
        $totalAmount = $unitFee + $additionalExpenses;

        // Dohvati vlasnike PRE generisanja QR koda
        $owners = $unit->owners()->get();

        // Generiši QR kod
        Log::info('=== QR KOD GENERISANJE START ===');
        Log::info('Unit ID: ' . $unit->id . ', Period: ' . $period);
        
        $callNumber = $this->qrGenerator->generateCallNumber($unit->id, $period);
        Log::info('Call Number: ' . $callNumber);
        
        $reference = $this->qrGenerator->formatReference('97', $callNumber);
        Log::info('Reference: ' . $reference);

        // Pripremi podatke o uplatiću (vlasnik)
        $payerName = null;
        $payerAddress = null;
        if ($owners->count() > 0) {
            $owner = $owners->first();
            // Formiraj puno ime i adresu uplatioca u jednom redu
            $payerName = $owner->full_name;
            if ($owner->address) {
                $payerName .= ', ' . $owner->address;
            }
            // Pošto je adresa već uključena u payer_name, ne treba dodatna adresa
            $payerAddress = null;
        }

        $qrPayloadData = [
            'recipient_account' => $community->bank_account_number,
            'recipient_name' => $community->name, // Samo naziv (max 70 karaktera)
            'currency' => 'RSD',
            'amount' => number_format($totalAmount, 2, '.', ''),
            'payment_code' => '189',
            'payment_purpose' => "Odrzavanje zgrade za {$periodDate->translatedFormat('F Y')}",
            'model_reference' => $reference,
            'payer_name' => $payerName,
            'payer_address' => $payerAddress,
        ];
        
        $qrPayload = $this->qrGenerator->generatePayload($qrPayloadData);
        $qrCodeSvg = $this->qrGenerator->generateQrCode($qrPayload, 200);

        return [
            'community' => $community,
            'unit' => $unit,
            'owners' => $owners,
            'period' => $periodDate,
            'pricing' => $pricing,
            'unitFee' => $unitFee,
            'expenses' => $expensesByCategory,
            'additionalExpenses' => $additionalExpenses,
            'totalAmount' => $totalAmount,
            'reference' => $reference,
            'callNumber' => $callNumber,
            'qrCode' => $qrCodeSvg,
            'dueDate' => $periodDate->copy()->addMonth()->day(10), // 10. sledećeg meseca
        ];
    }

    /**
     * Save statement to storage
     * 
     * @param \Barryvdh\DomPDF\PDF $pdf
     * @param string $period
     * @param string $identifier Unit or community identifier
     * @return string File path
     */
    public function saveStatement($pdf, string $period, string $identifier): string
    {
        $periodDate = Carbon::parse($period . '-01');
        $year = $periodDate->year;
        $month = $periodDate->format('m');
        
        $directory = storage_path("app/statements/{$year}/{$month}");
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filename = "statement_{$identifier}_{$year}_{$month}.pdf";
        $filepath = "{$directory}/{$filename}";
        
        $pdf->save($filepath);
        
        return $filepath;
    }

    /**
     * Kreiraj zapis u kartici stana (ledger) za mesečno zaduženje
     * 
     * @param Unit $unit
     * @param string $period Format: YYYY-MM
     * @param float $totalAmount
     * @return void
     */
    protected function createLedgerEntry(Unit $unit, string $period, float $totalAmount): void
    {
        // Proveri da li već postoji zaduženje za ovaj period
        $existingCharge = UnitLedger::where('unit_id', $unit->id)
            ->where('period', $period)
            ->where('type', 'charge')
            ->where('reference_type', 'billing_statement')
            ->first();
        
        if ($existingCharge) {
            return; // Već postoji zaduženje za ovaj period
        }

        if ($totalAmount <= 0) {
            return; // Nema zaduženja
        }

        $periodDate = Carbon::parse($period . '-01')->locale('sr_Latn');

        // Kreiraj stavku u ledger
        UnitLedger::createCharge(
            $unit->id,
            $periodDate->endOfMonth()->toDateString(),
            $totalAmount,
            "Mesečna uplatnica za " . $periodDate->translatedFormat('F Y'),
            'billing_statement',
            null,
            $period
        );
    }
}
