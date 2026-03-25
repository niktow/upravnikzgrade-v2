<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Expense;
use App\Models\UnitBillingStatement;
use App\Models\Unit;
use App\Models\UnitLedger;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\BillingStatementGenerator;

class PortalController extends Controller
{
    /**
     * Dohvati stanove povezane sa ulogovanim korisnikom
     */
    protected function getUserUnits()
    {
        $user = Auth::user();
        
        if (!$user->owner) {
            return collect();
        }

        return $user->owner->units()->where('is_active', true)->get();
    }

    /**
     * Dohvati stambene zajednice korisnika
     */
    protected function getUserCommunityIds()
    {
        return $this->getUserUnits()->pluck('housing_community_id')->unique();
    }

    /**
     * Dashboard - pregled
     */
    public function dashboard()
    {
        $units = $this->getUserUnits();
        $communityIds = $this->getUserCommunityIds();

        // Izračunaj ukupan saldo
        $totalBalance = 0;
        $unitBalances = [];

        foreach ($units as $unit) {
            $balance = $this->calculateUnitBalance($unit);
            $unitBalances[$unit->id] = $balance;
            $totalBalance += $balance['current_balance'];
        }

        // Poslednji oglasi
        $announcements = Announcement::whereIn('housing_community_id', $communityIds)
            ->active()
            ->pinnedFirst()
            ->take(5)
            ->get();

        return view('portal.dashboard', compact('units', 'totalBalance', 'unitBalances', 'announcements'));
    }

    /**
     * Stanje računa
     */
    public function balance()
    {
        $units = $this->getUserUnits();
        $unitBalances = [];

        foreach ($units as $unit) {
            $unitBalances[$unit->id] = $this->calculateUnitBalance($unit);
        }

        return view('portal.balance', compact('units', 'unitBalances'));
    }

    /**
     * Lista troškova
     */
    public function expenses(Request $request)
    {
        $communityIds = $this->getUserCommunityIds();
        $units = $this->getUserUnits();
        $unitIds = $units->pluck('id');

        $expenses = Expense::whereIn('housing_community_id', $communityIds)
            ->where(function ($query) use ($unitIds) {
                $query->whereNull('unit_id')
                    ->orWhereIn('unit_id', $unitIds);
            })
            ->with(['category', 'unit'])
            ->orderByDesc('incurred_on')
            ->paginate(20);

        return view('portal.expenses', compact('expenses', 'units'));
    }

    /**
     * Mesečni izvodi - prikazuje ledger unose
     */
    public function statements()
    {
        $units = $this->getUserUnits();
        $unitIds = $units->pluck('id');

        $ledgerEntries = UnitLedger::whereIn('unit_id', $unitIds)
            ->with('unit')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('portal.statements', compact('ledgerEntries', 'units'));
    }

    /**
     * Download PDF izvoda
     */
    public function downloadStatement(UnitBillingStatement $statement)
    {
        $units = $this->getUserUnits();
        
        // Provera da li korisnik ima pristup ovom izvodu
        if (!$units->contains('id', $statement->unit_id)) {
            abort(403);
        }

        // Ako postoji sačuvan PDF
        if ($statement->pdf_path && file_exists(storage_path('app/' . $statement->pdf_path))) {
            return response()->download(storage_path('app/' . $statement->pdf_path));
        }

        // Generiši PDF
        $unit = $statement->unit;
        $pdf = Pdf::loadView('billing.statement-simple', [
            'statement' => $statement,
            'unit' => $unit,
            'community' => $unit->housingCommunity,
        ])->setPaper('a4');

        return $pdf->download("izvod-{$unit->identifier}-{$statement->period}.pdf");
    }

    /**
     * Download računa iz ledger entry-ja
     */
    public function downloadLedgerStatement($ledgerId)
    {
        $units = $this->getUserUnits();
        $ledgerEntry = UnitLedger::findOrFail($ledgerId);
        
        // Provera da li korisnik ima pristup
        if (!$units->contains('id', $ledgerEntry->unit_id)) {
            abort(403);
        }

        // Proveri da li je zaduženje (samo za zaduženja se mogu preuzeti računi)
        if ($ledgerEntry->type !== 'charge' || !$ledgerEntry->period) {
            abort(404, 'Račun nije dostupan za ovu stavku');
        }

        // Proveri da li postoji sačuvani PDF
        $periodDate = \Carbon\Carbon::parse($ledgerEntry->period . '-01');
        $year = $periodDate->year;
        $month = $periodDate->format('m');
        $filepath = storage_path("app/statements/{$year}/{$month}/statement_{$ledgerEntry->unit->identifier}_{$year}_{$month}.pdf");

        if (file_exists($filepath)) {
            return response()->download($filepath);
        }

        // Ako ne postoji, generiši novi
        $generator = app(BillingStatementGenerator::class);
        $pdf = $generator->generateForUnit($ledgerEntry->unit, $ledgerEntry->period);
        
        return $pdf->download("racun_{$ledgerEntry->unit->identifier}_" . str_replace('-', '_', $ledgerEntry->period) . ".pdf");
    }

    /**
     * Oglasna tabla
     */
    public function announcements()
    {
        $communityIds = $this->getUserCommunityIds();

        $announcements = Announcement::whereIn('housing_community_id', $communityIds)
            ->active()
            ->pinnedFirst()
            ->paginate(10);

        return view('portal.announcements', compact('announcements'));
    }

    /**
     * Izračunaj trenutni saldo za stan - koristi UnitLedger karticu
     */
    protected function calculateUnitBalance(Unit $unit): array
    {
        // Dohvati saldo iz kartice stana
        $balanceDetails = UnitLedger::getBalanceDetails($unit->id);
        
        // Dohvati poslednje stavke za prikaz
        $recentEntries = UnitLedger::where('unit_id', $unit->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return [
            'total_charges' => $balanceDetails['total_charges'],
            'total_payments' => $balanceDetails['total_payments'],
            'current_balance' => $balanceDetails['current_balance'],
            'last_entry' => $balanceDetails['last_entry'],
            'recent_entries' => $recentEntries,
        ];
    }
}
