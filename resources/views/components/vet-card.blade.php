@props(['vet'])

<div class="group bg-white rounded-2xl border border-stone-200/70 p-4 sm:p-5 hover:shadow-xl hover:-translate-y-0.5 transition duration-200 flex flex-col">
    <div class="flex items-start gap-3 sm:gap-4">
        <a href="{{ route('vets.show', $vet) }}" class="shrink-0">
            @if ($vet->photoUrl())
                <img src="{{ $vet->photoUrl() }}" alt="{{ $vet->name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-stone-100">
            @else
                <span class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-sky-100 to-emerald-100 text-sky-800 text-2xl font-extrabold flex items-center justify-center">{{ mb_strtoupper(mb_substr(preg_replace('/^(dr\.?\s*)/i', '', $vet->name), 0, 1)) }}</span>
            @endif
        </a>
        <div class="min-w-0 flex-1">
            <a href="{{ route('vets.show', $vet) }}" class="block font-extrabold text-stone-900 truncate group-hover:text-amber-700">{{ $vet->name }}</a>
            @if ($vet->clinic_name)
                <p class="text-sm text-stone-600 truncate">{{ $vet->clinic_name }}</p>
            @endif
            <p class="text-xs text-stone-500 truncate">{{ collect([$vet->qualification, $vet->experience_years ? $vet->experience_years.' '.__('yrs experience') : null])->filter()->join(' · ') }}</p>
        </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-1.5">
        @foreach ($vet->categories->take(4) as $category)
            <span class="px-2 py-0.5 rounded-full bg-stone-100 text-[11px] font-semibold text-stone-700">{{ __($category->name) }}</span>
        @endforeach
        @if ($vet->isAiFound() && ! $vet->is_verified)
            <span class="px-2 py-0.5 rounded-full bg-violet-50 border border-violet-100 text-[11px] font-bold text-violet-700">🤖 {{ __('AI-found · unverified') }}</span>
        @endif
        @if ($vet->emergency)
            <span class="px-2 py-0.5 rounded-full bg-red-50 border border-red-100 text-[11px] font-bold text-red-700">🚨 {{ __('24x7 emergency') }}</span>
        @endif
        @if ($vet->home_visit)
            <span class="px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 text-[11px] font-bold text-emerald-700">🏠 {{ __('Home visit') }}</span>
        @endif
    </div>

    <div class="mt-3 flex items-center gap-1.5 text-xs text-stone-500 min-w-0">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <span class="truncate">{{ __($vet->city) }}</span>
        @isset($vet->distance_km)
            <span class="text-amber-700 font-semibold shrink-0">&middot; {{ $vet->distance_km < 1 ? __('In your city') : round($vet->distance_km).' km' }}</span>
        @endisset
        @if ($vet->consultation_fee !== null)
            <span class="ml-auto shrink-0 font-bold text-stone-800">{{ (float) $vet->consultation_fee > 0 ? inr($vet->consultation_fee, 0) : __('Free') }} <span class="font-normal text-stone-400">{{ __('visit') }}</span></span>
        @endif
    </div>

    <div class="mt-auto pt-4 grid grid-cols-2 gap-2">
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $vet->phone) }}" class="inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">📞 {{ __('Call') }}</a>
        <a href="{{ route('vets.show', $vet) }}" class="inline-flex items-center justify-center py-2.5 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 text-sm font-bold">{{ __('View profile') }}</a>
    </div>
</div>
