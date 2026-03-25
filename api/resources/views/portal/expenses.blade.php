@extends('portal.layouts.app')

@section('title', 'Troškovi - Stanarski Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Troškovi</h1>
    <p class="text-gray-600">Pregled svih troškova vaše stambene zajednice</p>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategorija</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Iznos</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $expense->incurred_on?->format('d.m.Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                {{ $expense->category->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-800">{{ Str::limit($expense->description, 50) }}</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($expense->unit)
                                {{ $expense->unit->identifier }}
                            @else
                                <span class="text-gray-400">Svi stanovi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-800">
                            {{ number_format($expense->amount, 2, ',', '.') }} RSD
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($expense->status === 'paid')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Plaćeno</span>
                            @elseif($expense->status === 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Na čekanju</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">{{ $expense->status }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p>Nema troškova za prikaz</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacija -->
    @if($expenses->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $expenses->links() }}
        </div>
    @endif
</div>
@endsection
