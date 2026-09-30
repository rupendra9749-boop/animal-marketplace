<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h2>{{ __('Compare Animals') }}</h2>
                <p class="text-sm text-stone-500 mt-1">{{ __('See price, breed, age and health side by side — then buy the best one.') }}</p>
            </div>
            @if ($animals->isNotEmpty())
                <form method="POST" action="{{ route('compare.clear') }}">
                    @csrf
                    @method('DELETE')
                    <x-secondary-button type="submit">{{ __('Clear all') }}</x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <x-flash />

        @if ($animals->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-stone-300 p-12 text-center max-w-2xl mx-auto">
                <p class="text-5xl">⚖️</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('Nothing to compare yet') }}</h3>
                <p class="mt-2 text-sm text-stone-500">{{ __('Tap "Compare" on any listing (up to 4), or add animals to your cart and compare them from there.') }}</p>
                <a href="{{ route('home') }}" class="inline-block mt-6">
                    <x-primary-button type="button">{{ __('Browse animals') }}</x-primary-button>
                </a>
            </div>
        @else
            @if ($bestPickId)
                @php $best = $animals->firstWhere('id', $bestPickId); @endphp
                <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <img src="{{ $best->imageUrl() }}" class="w-14 h-14 rounded-xl object-cover ring-2 ring-white/40">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-emerald-100 font-semibold">🏆 {{ __('Our best pick') }}</p>
                            <p class="text-lg font-extrabold">{{ $best->name }}</p>
                            <p class="text-sm text-emerald-100">
                                @if ($best->is_vaccinated && (float) $best->price === (float) $lowestPrice)
                                    {{ __('Vaccinated, in stock and the lowest price among your choices.') }}
                                @elseif ($best->is_vaccinated)
                                    {{ __('The cheapest vaccinated option that\'s in stock — safer than cheaper unvaccinated animals.') }}
                                @else
                                    {{ __('Lowest price among your choices.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @auth
                        @if ($best->stock > 0)
                            <form method="POST" action="{{ route('cart.store', $best) }}">
                                @csrf
                                <button class="bg-white text-emerald-700 font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-emerald-50">{{ __('Add best pick to cart') }}</button>
                            </form>
                        @endif
                    @endauth
                </div>
            @elseif ($animals->count() === 1)
                <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
                    {{ __('Add at least one more animal to see a side-by-side comparison and our recommendation.') }}
                    <a href="{{ route('home') }}" class="font-semibold underline">{{ __('Browse animals') }}</a>
                </div>
            @endif

            @if ($animals->count() >= 2)
                <a href="{{ route('breeding.index', ['a' => $animals[0]->id, 'b' => $animals[1]->id]) }}" class="flex items-center justify-between gap-4 rounded-2xl bg-white border border-stone-200/70 p-4 hover:shadow-lg transition">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-rose-50 text-2xl flex items-center justify-center">🧬</span>
                        <div>
                            <p class="font-bold text-stone-900">{{ __('Thinking of breeding these two?') }}</p>
                            <p class="text-sm text-stone-500">{{ __('Check breeding compatibility for') }} {{ $animals[0]->name }} + {{ $animals[1]->name }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-amber-700 shrink-0">{{ __('Check match') }} →</span>
                </a>
            @endif

            @php
                $rows = [
                    __('Type') => fn ($a) => __($a->category?->name ?? '—'),
                    __('Breed') => fn ($a) => $a->breed ?? '—',
                    __('Age') => fn ($a) => $a->age ?? '—',
                    __('Gender') => fn ($a) => __(ucfirst($a->gender)),
                    __('Color') => fn ($a) => $a->color ?? '—',
                    __('Weight') => fn ($a) => $a->weight ?? '—',
                    __('Location') => fn ($a) => $a->location ?? '—',
                    __('Seller') => fn ($a) => $a->seller->name,
                ];
            @endphp

            <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr>
                            <th class="w-40 p-4"></th>
                            @foreach ($animals as $animal)
                                <th class="p-4 align-top text-left border-l border-stone-100 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">
                                    <div class="relative">
                                        <form method="POST" action="{{ route('compare.destroy', $animal) }}" class="absolute -top-1 -right-1">
                                            @csrf
                                            @method('DELETE')
                                            <button title="{{ __('Remove') }}" class="w-7 h-7 rounded-full bg-white shadow border border-stone-200 text-stone-400 hover:text-red-500">&times;</button>
                                        </form>
                                        <a href="{{ route('animals.show', $animal) }}">
                                            <img src="{{ $animal->imageUrl() }}" class="w-36 h-36 object-cover rounded-xl bg-stone-100">
                                        </a>
                                        @if ($animal->id === $bestPickId)
                                            <span class="inline-block mt-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white text-[11px] font-bold">🏆 {{ __('Best pick') }}</span>
                                        @endif
                                        <a href="{{ route('animals.show', $animal) }}" class="block mt-2 font-bold text-stone-900 hover:text-amber-700">{{ $animal->name }}</a>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr>
                            <td class="p-4 font-semibold text-stone-500">{{ __('Price') }}</td>
                            @foreach ($animals as $animal)
                                <td class="p-4 border-l border-stone-100 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">
                                    <span class="text-xl font-extrabold text-stone-900">{{ inr($animal->price, 0) }}</span>
                                    @if ($animals->count() > 1 && (float) $animal->price === (float) $lowestPrice)
                                        <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[11px] font-bold">{{ __('Lowest') }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-semibold text-stone-500">{{ __('Vaccinated') }}</td>
                            @foreach ($animals as $animal)
                                <td class="p-4 border-l border-stone-100 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">
                                    @if ($animal->is_vaccinated)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold">✓ {{ __('Yes') }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-red-600 font-semibold">✕ {{ __('No') }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @foreach ($rows as $label => $value)
                            <tr>
                                <td class="p-4 font-semibold text-stone-500">{{ $label }}</td>
                                @foreach ($animals as $animal)
                                    <td class="p-4 border-l border-stone-100 text-stone-800 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">{{ $value($animal) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr>
                            <td class="p-4 font-semibold text-stone-500">{{ __('Availability') }}</td>
                            @foreach ($animals as $animal)
                                <td class="p-4 border-l border-stone-100 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">
                                    <span class="{{ $animal->stock > 0 ? 'text-emerald-700' : 'text-red-600' }} font-semibold">
                                        {{ $animal->stock > 0 ? __(':count available', ['count' => $animal->stock]) : __('Sold out') }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4"></td>
                            @foreach ($animals as $animal)
                                <td class="p-4 border-l border-stone-100 {{ $animal->id === $bestPickId ? 'bg-emerald-50/60' : '' }}">
                                    @auth
                                        @if ($animal->stock > 0)
                                            <form method="POST" action="{{ route('cart.store', $animal) }}">
                                                @csrf
                                                <x-primary-button class="w-full justify-center">{{ __('Add to cart') }}</x-primary-button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-amber-700 hover:underline">{{ __('Log in to buy') }}</a>
                                    @endauth
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($animals->count() < \App\Http\Controllers\CompareController::MAX_ITEMS)
                <p class="text-center text-sm text-stone-500">
                    {{ __('You can add :count more.', ['count' => \App\Http\Controllers\CompareController::MAX_ITEMS - $animals->count()]) }}
                    <a href="{{ route('home') }}" class="font-semibold text-amber-700 hover:underline">{{ __('Browse animals') }}</a>
                </p>
            @endif
        @endif
    </div>
</x-app-layout>
