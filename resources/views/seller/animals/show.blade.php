@php $area = $area ?? 'seller'; @endphp
<x-panel-layout>
    <x-slot name="back">{{ route($area.'.animals.index') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-3 min-w-0">
            <h2 class="truncate">{{ $animal->name }}</h2>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 max-w-5xl">
        <div class="lg:col-span-2">
            <img src="{{ $animal->imageUrl() }}" alt="{{ $animal->name }}" class="w-full aspect-[4/3] object-cover rounded-2xl bg-stone-100 border border-stone-200/70">
        </div>

        <div class="lg:col-span-3 bg-white rounded-2xl border border-stone-200/70 p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ __($animal->category?->name ?? 'Uncategorized') }}</p>
                    <h1 class="text-2xl font-extrabold text-stone-900 mt-0.5">{{ $animal->name }}</h1>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $animal->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">{{ $animal->is_active ? __('Visible') : __('Hidden') }}</span>
            </div>

            @if ($animal->isForSale())
                <p class="mt-4 text-3xl font-extrabold text-stone-900">{{ inr($animal->price, 2) }}
                    <span class="text-sm font-semibold {{ $animal->stock > 0 ? 'text-sky-700' : 'text-red-600' }}">· {{ $animal->stock > 0 ? $animal->stock.' '.__('in stock') : __('Sold out') }}</span>
                </p>
            @endif
            @if ($animal->offersBreeding())
                <p class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-100 text-sm font-bold text-rose-700">
                    🧬 {{ $animal->isBreedingOnly() ? __('Breeding only') : __('Also for breeding') }}
                    &middot; {{ (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee, 2).' '.__('per service') : __('fee: ask owner') }}
                </p>
            @endif

            <dl class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                @foreach ([
                    [__('Breed'), $animal->breed], [__('Age'), $animal->age], [__('Gender'), __(ucfirst($animal->gender))],
                    [__('Color'), $animal->color], [__('Weight'), $animal->weight], [__('Location'), $animal->location],
                    [__('Vaccinated'), $animal->is_vaccinated ? __('Yes') : __('No')],
                ] as [$label, $value])
                    <div class="rounded-xl bg-stone-50 p-3">
                        <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ $label }}</dt>
                        <dd class="mt-0.5 font-semibold text-stone-800">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            <p class="mt-5 text-stone-600 whitespace-pre-line text-sm leading-relaxed">{{ $animal->description ?: __('No description provided.') }}</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route($area.'.animals.edit', $animal) }}"><x-primary-button type="button">{{ __('Edit listing') }}</x-primary-button></a>
                <a href="{{ route('animals.show', $animal) }}"><x-secondary-button type="button">{{ __('View public page') }}</x-secondary-button></a>
                <form method="POST" action="{{ route($area.'.animals.destroy', $animal) }}" onsubmit="return confirm('{{ __('Delete this animal listing?') }}')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-panel-layout>
