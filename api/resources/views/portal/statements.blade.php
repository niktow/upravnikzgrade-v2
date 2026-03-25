@extends('portal.layouts.app')

@section('title', 'Računi - Stanarski Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Računi i kartica stana</h1>
    <p class="text-gray-600">Pregled zaduženja, uplata i preuzimanje mesečnih računa</p>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tip</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Iznos</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Akcije</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($ledgerEntries as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $entry->date->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                            {{ $entry->unit->identifier }}
                        </td>
                        <td class="px-6 py-4 text-gray-800">
                            {{ $entry->description }}
                            @if($entry->period)
                                <span class="text-xs text-gray-500 block mt-1">
                                    Period: {{ \Carbon\Carbon::parse($entry->period . '-01')->locale('sr_Latn')->translatedFormat('F Y') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->type === 'charge')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Zaduženje
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Uplata
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold {{ $entry->type === 'charge' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $entry->type === 'charge' ? '+' : '-' }}{{ number_format($entry->amount, 2, ',', '.') }} RSD
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($entry->type === 'charge' && $entry->period)
                                <a href="{{ route('portal.statements.ledger.download', $entry->id) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Preuzmi račun
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p>Nema stavki za prikaz</p>
                            <p class="text-sm mt-1">Stavke će biti vidljive nakon generisanja prvog računa ili evidentiranja uplate.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginacija -->
    @if($ledgerEntries->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $ledgerEntries->links() }}
        </div>
    @endif
</div>
@endsection
