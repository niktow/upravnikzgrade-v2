@extends('portal.layouts.app')

@section('title', 'Oglasna tabla - Stanarski Portal')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Oglasna tabla</h1>
    <p class="text-gray-600">Obaveštenja i informacije vaše stambene zajednice</p>
</div>

<div class="space-y-4">
    @forelse($announcements as $announcement)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b {{ $announcement->priority === 'urgent' ? 'bg-red-50' : ($announcement->priority === 'high' ? 'bg-orange-50' : 'bg-gray-50') }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        @if($announcement->is_pinned)
                            <span class="text-yellow-500 text-xl">📌</span>
                        @endif
                        
                        <!-- Ikonica tipa -->
                        @switch($announcement->type)
                            @case('warning')
                                <span class="p-2 bg-yellow-100 rounded-full">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </span>
                                @break
                            @case('maintenance')
                                <span class="p-2 bg-blue-100 rounded-full">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                @break
                            @case('meeting')
                                <span class="p-2 bg-purple-100 rounded-full">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </span>
                                @break
                            @case('financial')
                                <span class="p-2 bg-green-100 rounded-full">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                @break
                            @default
                                <span class="p-2 bg-gray-100 rounded-full">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                        @endswitch
                        
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">{{ $announcement->title }}</h2>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="px-2 py-0.5 text-xs rounded-full 
                                    @if($announcement->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($announcement->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($announcement->priority === 'low') bg-gray-100 text-gray-600
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ $announcement->priority_label }}
                                </span>
                                <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">
                                    {{ $announcement->type_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right text-sm text-gray-500">
                        <p>{{ $announcement->created_at->format('d.m.Y') }}</p>
                        <p>{{ $announcement->created_at->format('H:i') }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                <div class="prose max-w-none text-gray-700">
                    {!! $announcement->content !!}
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-3 bg-gray-50 border-t text-sm text-gray-500">
                Objavio: {{ $announcement->creator->name ?? 'Upravnik' }} • {{ $announcement->created_at->diffForHumans() }}
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-600">Nema obaveštenja</h3>
            <p class="text-gray-500 mt-2">Trenutno nema aktivnih obaveštenja za vašu stambenu zajednicu.</p>
        </div>
    @endforelse
</div>

<!-- Paginacija -->
@if($announcements->hasPages())
    <div class="mt-6">
        {{ $announcements->links() }}
    </div>
@endif
@endsection
