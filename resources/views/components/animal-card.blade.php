@props(['animal', 'wishlisted' => false, 'compared' => false, 'mode' => 'shop'])

@php
    $breeding = $mode === 'breeding';
    $role = match ($animal->gender) {
        'male' => '♂ '.__('Stud'),
        'female' => '♀ '.__('Dam'),
        default => null,
    };
@endphp

<div class="group bg-white rounded-2xl border border-stone-200/70 overflow-hidden hover:shadow-xl hover:-translate-y-0.5 transition duration-200 flex flex-col">
    <div class="relative">
        <a href="{{ route('animals.show', $animal) }}" class="block overflow-hidden aspect-[4/3] bg-stone-100">
            <img src="{{ $animal->imageUrl() }}" alt="{{ $animal->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </a>

        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-wrap gap-1">
            <span class="px-2 py-0.5 rounded-full bg-white/95 text-[10px] sm:text-[11px] font-semibold text-stone-700 shadow-sm">{{ __($animal->category?->name ?? 'Animal') }}</span>
            @if ($animal->is_vaccinated)
                <span class="px-2 py-0.5 rounded-full bg-emerald-500 text-[10px] sm:text-[11px] font-semibold text-white shadow-sm">✓<span class="hidden sm:inline"> {{ __('Vaccinated') }}</span></span>
            @endif
        </div>

        @if ($breeding && $role)
            <span class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 px-2.5 py-1 rounded-full bg-rose-600 text-[11px] font-bold text-white shadow">{{ $role }}</span>
        @elseif (! $breeding && $animal->offersBreeding())
            <span class="absolute bottom-2 left-2 sm:bottom-3 sm:left-3 px-2.5 py-1 rounded-full bg-rose-600 text-[11px] font-bold text-white shadow">🧬 {{ __('Breeding') }}</span>
        @endif

        @auth
            <form method="POST" action="{{ $wishlisted ? route('wishlist.destroy', $animal) : route('wishlist.store', $animal) }}" class="absolute top-2 right-2 sm:top-3 sm:right-3">
                @csrf
                @if ($wishlisted) @method('DELETE') @endif
                <button type="submit" title="{{ __('Wishlist') }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white/95 shadow flex items-center justify-center transition {{ $wishlisted ? 'text-red-500' : 'text-stone-400 hover:text-red-500' }}">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
                </button>
            </form>
        @endauth
    </div>

    <div class="p-3 sm:p-4 flex-1 flex flex-col">
        <a href="{{ route('animals.show', $animal) }}" class="font-bold text-sm sm:text-base text-stone-900 truncate hover:text-amber-700">{{ $animal->name }}</a>
        <p class="text-[11px] sm:text-xs text-stone-500 mt-0.5 truncate">
            {{ collect([$animal->breed, $animal->age, $animal->gender !== 'unknown' ? __(ucfirst($animal->gender)) : null])->filter()->join(' · ') }}
        </p>

        <div class="flex items-center gap-1 text-[11px] sm:text-xs text-stone-500 mt-2 min-w-0">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="truncate">{{ __($animal->location) ?? __('Location not set') }}</span>
            @isset($animal->distance_km)
                <span class="text-amber-700 font-semibold shrink-0">&middot; {{ round($animal->distance_km) }} km</span>
            @endisset
        </div>

        <div class="mt-auto pt-3 sm:pt-4 flex items-center justify-between gap-2">
            @if ($breeding)
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-wider font-bold text-stone-400">{{ __('Breeding fee') }}</p>
                    <p class="text-base sm:text-lg font-extrabold text-stone-900 leading-tight">{{ $animal->breeding_fee !== null && (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee, 0) : __('Ask owner') }}</p>
                </div>
                <a href="{{ route('breeding.index', ['a' => $animal->id]) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 whitespace-nowrap">🧬<span class="hidden sm:inline ml-1">{{ __('Find match') }}</span></a>
            @else
                <p class="text-base sm:text-lg font-extrabold text-stone-900">{{ inr($animal->price, 0) }}</p>

                <form method="POST" action="{{ $compared ? route('compare.destroy', $animal) : route('compare.store', $animal) }}">
                    @csrf
                    @if ($compared) @method('DELETE') @endif
                    <button type="submit" title="{{ $compared ? __('Comparing') : __('Compare') }}" class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition {{ $compared ? 'bg-amber-600 border-amber-600 text-white' : 'border-stone-200 text-stone-600 hover:border-amber-500 hover:text-amber-700' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12M8 7l4-4M8 7l4 4M16 17H4m12 0l-4-4m4 4l-4 4"/></svg>
                        <span class="hidden sm:inline">{{ $compared ? __('Comparing') : __('Compare') }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
