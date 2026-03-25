<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\UnitLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isTenant()) {
            abort(403, 'Nemate pristup tenant dashboard endpoint-u.');
        }

        if (!$user->owner_id) {
            return response()->json([
                'message' => 'Tenant korisnik nema dodeljen owner_id.',
                'stats' => [
                    'units_count' => 0,
                    'total_balance' => 0,
                ],
                'units' => [],
            ], 200);
        }

        $units = Unit::query()
            ->whereHas('owners', function ($query) use ($user) {
                $query->where('owners.id', $user->owner_id);
            })
            ->with(['housingCommunity:id,name', 'owners:id,full_name'])
            ->withSum([
                'ledgerEntries as total_charges' => function ($query) {
                    $query->where('type', 'charge');
                }
            ], 'amount')
            ->withSum([
                'ledgerEntries as total_payments' => function ($query) {
                    $query->where('type', 'payment');
                }
            ], 'amount')
            ->orderByDesc('id')
            ->get();

        $mappedUnits = $units->map(function (Unit $unit) {
            $charges = (float) ($unit->total_charges ?? 0);
            $payments = (float) ($unit->total_payments ?? 0);

            return [
                'id' => $unit->id,
                'identifier' => $unit->identifier,
                'type' => $unit->type,
                'housing_community' => optional($unit->housingCommunity)->name,
                'owner_names' => $unit->owners->pluck('full_name')->values(),
                'current_balance' => $charges - $payments,
            ];
        });

        $totalBalance = (float) UnitLedger::query()
            ->whereIn('unit_id', $units->pluck('id'))
            ->selectRaw("SUM(CASE WHEN type = 'charge' THEN amount ELSE -amount END) as balance")
            ->value('balance');

        return response()->json([
            'stats' => [
                'units_count' => $units->count(),
                'total_balance' => $totalBalance,
            ],
            'units' => $mappedUnits,
        ]);
    }
}
