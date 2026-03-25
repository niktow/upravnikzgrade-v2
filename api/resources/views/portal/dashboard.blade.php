@extends('portal.layouts.app')

@section('title', 'Početna - Stanarski Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dobrodošli, {{ auth()->user()->owner->full_name ?? auth()->user()->name }}!</h1>
    <p class="text-gray-600">Pregled vašeg stanja i najnovijih obaveštenja</p>
</div>

<!-- Saldo kartica -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Trenutni saldo</p>
                <p class="text-3xl font-bold {{ $totalBalance > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($totalBalance, 2, ',', '.') }} RSD
                </p>
            </div>
            <div class="p-3 rounded-full {{ $totalBalance > 0 ? 'bg-red-100' : 'bg-green-100' }}">
                <svg class="w-8 h-8 {{ $totalBalance > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        @if($totalBalance > 0)
            <p class="mt-2 text-sm text-red-600">Imate neizmirene obaveze</p>
        @else
            <p class="mt-2 text-sm text-green-600">Sve obaveze su izmirene</p>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Broj stanova</p>
                <p class="text-3xl font-bold text-gray-800">{{ $units->count() }}</p>
            </div>
            <div class="p-3 rounded-full bg-blue-100">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Novih oglasa</p>
                <p class="text-3xl font-bold text-gray-800">{{ $announcements->count() }}</p>
            </div>
            <div class="p-3 rounded-full bg-yellow-100">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Stanje po stanovima -->
@if($units->count() > 0)
<div class="bg-white rounded-lg shadow mb-8">
    <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold text-gray-800">Stanje po stanovima</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zgrada</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Zaduženja</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Uplate</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($units as $unit)
                    @php $balance = $unitBalances[$unit->id]; @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $unit->identifier }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $unit->housingCommunity->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600">{{ number_format($balance['total_charges'], 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-600">{{ number_format($balance['total_payments'], 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold {{ $balance['current_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($balance['current_balance'], 2, ',', '.') }} RSD
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Poslednji oglasi -->
@if($announcements->count() > 0)
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Poslednji oglasi</h2>
        <a href="{{ route('portal.announcements') }}" class="text-blue-600 hover:text-blue-800 text-sm">Vidi sve →</a>
    </div>
    <div class="divide-y divide-gray-200">
        @foreach($announcements as $announcement)
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            @if($announcement->is_pinned)
                                <span class="text-yellow-500">📌</span>
                            @endif
                            <h3 class="font-semibold text-gray-800">{{ $announcement->title }}</h3>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($announcement->priority === 'urgent') bg-red-100 text-red-800
                                @elseif($announcement->priority === 'high') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $announcement->priority_label }}
                            </span>
                        </div>
                        <p class="mt-2 text-gray-600 line-clamp-2">{{ Str::limit($announcement->content, 200) }}</p>
                        <p class="mt-2 text-sm text-gray-400">{{ $announcement->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
    </svg>
    <p>Nema novih obaveštenja</p>
</div>
@endif
@endsection
