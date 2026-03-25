<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Unit;
use App\Models\UnitLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isManager()) {
            abort(403, 'Nemate pristup admin dashboard endpoint-u.');
        }

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $monthlyCharges = (float) UnitLedger::where('type', 'charge')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');

        $monthlyPayments = (float) UnitLedger::where('type', 'payment')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('amount');

        $totalCharges = (float) UnitLedger::where('type', 'charge')->sum('amount');
        $totalPayments = (float) UnitLedger::where('type', 'payment')->sum('amount');

        return response()->json([
            'period' => [
                'from' => $periodStart->toDateString(),
                'to' => $periodEnd->toDateString(),
            ],
            'stats' => [
                'total_units' => Unit::count(),
                'active_units' => Unit::where('is_active', true)->count(),
                'total_owners' => Owner::count(),
                'monthly_charges' => $monthlyCharges,
                'monthly_payments' => $monthlyPayments,
                'total_balance' => $totalCharges - $totalPayments,
            ],
        ]);
    }
}
