<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isManager()) {
            abort(403, 'Nemate pristup admin units endpoint-u.');
        }

        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage <= 0) {
            $perPage = 20;
        }

        $query = Unit::query()
            ->with(['housingCommunity:id,name', 'owners:id,full_name'])
            ->withSum([
                'ledgerEntries as total_charges' => function ($ledgerQuery) {
                    $ledgerQuery->where('type', 'charge');
                }
            ], 'amount')
            ->withSum([
                'ledgerEntries as total_payments' => function ($ledgerQuery) {
                    $ledgerQuery->where('type', 'payment');
                }
            ], 'amount')
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('identifier', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('owners', function ($ownerQuery) use ($search) {
                            $ownerQuery->where('full_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('housingCommunity', function ($communityQuery) use ($search) {
                            $communityQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('id');

        $paginated = $query->paginate($perPage)->withQueryString();

        $items = $paginated->getCollection()->map(function (Unit $unit) {
            $charges = (float) ($unit->total_charges ?? 0);
            $payments = (float) ($unit->total_payments ?? 0);

            return [
                'id' => $unit->id,
                'identifier' => $unit->identifier,
                'type' => $unit->type,
                'is_active' => (bool) $unit->is_active,
                'area' => $unit->area,
                'occupant_count' => $unit->occupant_count,
                'housing_community' => optional($unit->housingCommunity)->name,
                'owner_names' => $unit->owners->pluck('full_name')->values(),
                'current_balance' => $charges - $payments,
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'search' => $search,
            ],
        ]);
    }
}
