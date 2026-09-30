@php $area = $area ?? 'seller'; @endphp
<x-panel-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2>{{ __('My Animals') }}</h2>
            <a href="{{ route($area.'.animals.create') }}"><x-primary-button type="button">+ {{ __('Add animal') }}</x-primary-button></a>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        @forelse ($animals as $animal)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 sm:px-6 py-4 border-b border-stone-100 last:border-0">
                <a href="{{ route($area.'.animals.show', $animal) }}" class="flex items-center gap-4 flex-1 min-w-0">
                    <img src="{{ $animal->imageUrl() }}" class="w-16 h-16 rounded-xl object-cover bg-stone-100 shrink-0" alt="">
                    <div class="min-w-0">
                        <p class="font-bold text-stone-900 truncate hover:text-amber-700">{{ $animal->name }}</p>
                        <p class="text-xs text-stone-500 truncate">{{ collect([$animal->breed, $animal->age, __($animal->location)])->filter()->join(' · ') ?: '—' }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $animal->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">{{ $animal->is_active ? __('Visible') : __('Hidden') }}</span>
                            @if ($animal->isForSale())
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $animal->stock > 5 ? 'bg-sky-50 text-sky-700' : ($animal->stock > 0 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">{{ $animal->stock > 0 ? $animal->stock.' '.__('in stock') : __('Sold out') }}</span>
                            @endif
                            @if ($animal->offersBreeding())
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">🧬 {{ $animal->isBreedingOnly() ? __('Breeding only') : __('Also breeding') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                <div class="flex items-center justify-between sm:justify-end gap-4 sm:w-72">
                    <p class="text-lg font-extrabold text-stone-900">
                        @if ($animal->isBreedingOnly())
                            <span class="block text-[10px] uppercase tracking-wider font-bold text-stone-400 leading-none">{{ __('Breeding fee') }}</span>{{ (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee, 0) : '—' }}
                        @else
                            {{ inr($animal->price, 0) }}
                        @endif
                    </p>
                    <div class="flex items-center gap-2 text-sm font-semibold">
                        <a href="{{ route($area.'.animals.show', $animal) }}" class="px-3 py-1.5 rounded-lg text-stone-600 hover:bg-stone-100">{{ __('View') }}</a>
                        <a href="{{ route($area.'.animals.edit', $animal) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('Edit') }}</a>
                        <form method="POST" action="{{ route($area.'.animals.destroy', $animal) }}" onsubmit="return confirm('{{ __('Delete this animal listing?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <p class="text-5xl">🐄</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('No animals listed yet') }}</h3>
                <p class="text-sm text-stone-500 mt-1">{{ __('Add your first animal and reach buyers near you.') }}</p>
                <a href="{{ route($area.'.animals.create') }}" class="inline-block mt-6"><x-primary-button type="button">+ {{ __('Add your first animal') }}</x-primary-button></a>
            </div>
        @endforelse
    </div>

    {{ $animals->links() }}
</x-panel-layout>
