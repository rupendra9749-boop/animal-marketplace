<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('All Animals') }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('All animals')" :subtitle="$animals->total().' '.__('listings from every seller. Hide or remove anything that should not be public.')" />

    <div class="bg-white rounded-2xl border border-stone-200/70 p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => __('All'), 'sale' => __('For sale'), 'breeding' => __('Breeding')] as $value => $label)
                <a href="{{ route('admin.animals.index', array_filter(['listing' => $value, 'search' => request('search')])) }}"
                   class="px-3.5 py-2 rounded-full text-sm font-semibold transition {{ request('listing', '') === $value ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2">
            @if (request('listing')) <input type="hidden" name="listing" value="{{ request('listing') }}"> @endif
            <x-text-input name="search" class="w-full md:w-64" value="{{ request('search') }}" placeholder="{{ __('Search animals...') }}" />
            <x-primary-button>{{ __('Search') }}</x-primary-button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[720px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Animal') }}</th>
                    <th class="px-5 py-3">{{ __('Seller') }}</th>
                    <th class="px-5 py-3">{{ __('Price') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($animals as $animal)
                    <tr>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.animals.show', $animal) }}" class="flex items-center gap-3 min-w-0">
                                <img src="{{ $animal->imageUrl() }}" alt="" class="w-11 h-11 rounded-xl object-cover bg-stone-100 shrink-0">
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 truncate hover:text-amber-700">{{ $animal->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">
                                        {{ collect([__($animal->category?->name), __($animal->location)])->filter()->join(' · ') ?: '—' }}
                                        @if ($animal->offersBreeding())
                                            <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">🧬 {{ $animal->isBreedingOnly() ? __('Breeding only') : __('+ breeding') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3 text-stone-700">{{ $animal->seller->name }}</td>
                        <td class="px-5 py-3 font-semibold text-stone-900">{{ $animal->isBreedingOnly() ? inr((float) $animal->breeding_fee, 0).' '.__('fee') : inr($animal->price, 0) }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.animals.update', $animal) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $animal->is_active ? 0 : 1 }}">
                                <button type="submit" title="{{ $animal->is_active ? __('Click to hide') : __('Click to show') }}" class="px-2.5 py-1 rounded-full text-xs font-bold {{ $animal->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 hover:bg-stone-200' }}">
                                    {{ $animal->is_active ? __('Active') : __('Hidden') }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                <a href="{{ route('seller.animals.edit', $animal) }}" class="px-3 py-1.5 rounded-lg text-stone-600 hover:bg-stone-100">{{ __('Edit') }}</a>
                                <a href="{{ route('admin.animals.show', $animal) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('View') }}</a>
                                <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" onsubmit="return confirm('{{ __('Delete this animal listing?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <p class="text-4xl">🐾</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No animals found') }}</p>
                            <p class="text-sm text-stone-500 mt-1">{{ __('Try a different search or filter.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $animals->links() }}
</x-admin-layout>
