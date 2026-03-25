@extends('portal.layouts.app')

@section('title', 'Stanje računa - Stanarski Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Stanje računa</h1>
    <p class="text-gray-600">Detaljan pregled zaduženja i uplata po stanovima</p>
</div>

@forelse($units as $unit)
    @php $balance = $unitBalances[$unit->id]; @endphp
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Stan: {{ $unit->identifier }}</h2>
                    <p class="text-sm text-gray-600">{{ $unit->housingCommunity->name }} | {{ $unit->housingCommunity->address_line }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Trenutni saldo</p>
                    <p class="text-2xl font-bold {{ $balance['current_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($balance['current_balance'], 2, ',', '.') }} RSD
                    </p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Ukupna zaduženja -->
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-red-600 font-medium">Ukupna zaduženja</p>
                            <p class="text-2xl font-bold text-red-700">{{ number_format($balance['total_charges'], 2, ',', '.') }}</p>
                        </div>
                        <div class="p-2 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Ukupne uplate -->
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Ukupne uplate</p>
                            <p class="text-2xl font-bold text-green-700">{{ number_format($balance['total_payments'], 2, ',', '.') }}</p>
                        </div>
                        <div class="p-2 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Saldo -->
                <div class="{{ $balance['current_balance'] > 0 ? 'bg-orange-50' : 'bg-blue-50' }} rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm {{ $balance['current_balance'] > 0 ? 'text-orange-600' : 'text-blue-600' }} font-medium">
                                {{ $balance['current_balance'] > 0 ? 'Dugovanje' : 'Stanje' }}
                            </p>
                            <p class="text-2xl font-bold {{ $balance['current_balance'] > 0 ? 'text-orange-700' : 'text-blue-700' }}">
                                {{ number_format(abs($balance['current_balance']), 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="p-2 {{ $balance['current_balance'] > 0 ? 'bg-orange-100' : 'bg-blue-100' }} rounded-full">
                            <svg class="w-6 h-6 {{ $balance['current_balance'] > 0 ? 'text-orange-600' : 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Poslednja stavka -->
            @if(isset($balance['last_entry']) && $balance['last_entry'])
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Poslednja stavka:</span> 
                        {{ $balance['last_entry']->description }} 
                        ({{ $balance['last_entry']->date->format('d.m.Y') }})
                    </p>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-600">Nema povezanih stanova</h3>
        <p class="text-gray-500 mt-2">Kontaktirajte upravnika zgrade za povezivanje vašeg naloga sa stanom.</p>
    </div>
@endforelse
@endsection
