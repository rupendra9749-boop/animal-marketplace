<x-app-layout>
    <x-slot name="title">{{ $animal->name }}</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">
        <nav class="text-sm text-stone-500 flex flex-wrap items-center gap-x-2 gap-y-1">
            <a href="{{ route('home') }}" class="hover:text-amber-700">{{ __('Home') }}</a>
            <span>/</span>
            @if ($animal->isBreedingOnly())
                <a href="{{ route('breeding.browse') }}" class="hover:text-amber-700">{{ __('Breeding animals') }}</a>
                <span>/</span>
            @elseif ($animal->category)
                <a href="{{ route('home', ['category' => $animal->category_id]) }}" class="hover:text-amber-700">{{ __($animal->category->name) }}</a>
                <span>/</span>
            @endif
            <span class="text-stone-800 font-medium truncate">{{ $animal->name }}</span>
        </nav>

        <x-flash />

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">
            {{-- Photo --}}
            <div class="lg:col-span-3">
                <div class="rounded-3xl overflow-hidden bg-stone-100 border border-stone-200/70 aspect-[4/3]">
                    <img src="{{ $animal->imageUrl() }}" alt="{{ $animal->name }}" class="w-full h-full object-cover">
                </div>
            </div>

            {{-- Buy / breeding box (sits right under the photo on phones) --}}
            <div class="lg:col-span-2 lg:col-start-4 lg:row-start-1 lg:row-span-2">
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6 lg:sticky lg:top-24">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ __($animal->category?->name ?? 'Animal') }}</p>
                            <h1 class="mt-1 text-2xl font-extrabold text-stone-900">{{ $animal->name }}</h1>
                        </div>
                        @auth
                            <form method="POST" action="{{ $isWishlisted ? route('wishlist.destroy', $animal) : route('wishlist.store', $animal) }}" class="shrink-0">
                                @csrf
                                @if ($isWishlisted) @method('DELETE') @endif
                                <button type="submit" title="{{ __('Wishlist') }}" class="w-11 h-11 rounded-full border border-stone-200 flex items-center justify-center {{ $isWishlisted ? 'text-red-500 bg-red-50 border-red-200' : 'text-stone-400 hover:text-red-500' }}">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
                                </button>
                            </form>
                        @endauth
                    </div>

                    <div class="flex flex-wrap gap-2 mt-4">
                        @if ($animal->isBreedingOnly())
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">🧬 {{ __('Breeding only') }}</span>
                        @elseif ($animal->offersBreeding())
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">🧬 {{ __('Also available for breeding') }}</span>
                        @endif
                        @if ($animal->is_vaccinated)
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ {{ __('Vaccinated') }}</span>
                        @endif
                        @if ($animal->isForSale())
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $animal->stock > 0 ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                {{ $animal->stock > 0 ? __(':count available', ['count' => $animal->stock]) : __('Sold out') }}
                            </span>
                        @endif
                    </div>

                    @if ($animal->isForSale())
                        <p class="mt-5 text-4xl font-extrabold text-stone-900">{{ inr($animal->price, 2) }}</p>

                        @auth
                            @if ($animal->stock > 0)
                                <form method="POST" action="{{ route('cart.store', $animal) }}" class="mt-5 flex gap-3">
                                    @csrf
                                    <x-text-input name="quantity" type="number" min="1" max="{{ $animal->stock }}" value="1" class="w-20" aria-label="{{ __('Quantity') }}" />
                                    <x-primary-button class="flex-1 justify-center py-3">{{ __('Add to Cart') }}</x-primary-button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="mt-5 block">
                                <x-primary-button type="button" class="w-full justify-center py-3">{{ __('Log in to buy') }}</x-primary-button>
                            </a>
                        @endauth

                        <form method="POST" action="{{ $isCompared ? route('compare.destroy', $animal) : route('compare.store', $animal) }}" class="mt-3">
                            @csrf
                            @if ($isCompared) @method('DELETE') @endif
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-lg border text-sm font-semibold transition {{ $isCompared ? 'border-amber-600 bg-amber-50 text-amber-800' : 'border-stone-200 text-stone-700 hover:border-amber-500 hover:text-amber-700' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12M8 7l4-4M8 7l4 4M16 17H4m12 0l-4-4m4 4l-4 4"/></svg>
                                {{ $isCompared ? __('Added to compare — remove') : __('Add to compare') }}
                            </button>
                        </form>
                    @endif

                    @if ($animal->offersBreeding())
                        <div class="mt-5 rounded-2xl bg-rose-50 border border-rose-100 p-4">
                            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">🧬 {{ __('Breeding fee') }}</p>
                            <p class="mt-1 text-3xl font-extrabold text-stone-900">
                                {{ $animal->breeding_fee !== null && (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee, 2) : __('Ask owner') }}
                                @if ($animal->breeding_fee !== null && (float) $animal->breeding_fee > 0)
                                    <span class="text-sm font-semibold text-stone-500">/ {{ __('service') }}</span>
                                @endif
                            </p>
                            @auth
                                @if ($animal->user_id !== auth()->id())
                                    <form method="POST" action="{{ route('animals.messages.start', $animal) }}" class="mt-3">
                                        @csrf
                                        <textarea name="body" rows="3" required class="block w-full border-rose-200 rounded-xl text-sm focus:border-rose-500 focus:ring-rose-500">{{ __('Hi, I would like to use :name for breeding. Is it available, and what are your terms?', ['name' => $animal->name]) }}</textarea>
                                        <button type="submit" class="mt-2 w-full inline-flex items-center justify-center gap-2 py-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">💬 {{ __('Request breeding') }}</button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="mt-3 block text-center py-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">{{ __('Log in to contact the owner') }}</a>
                            @endauth
                        </div>
                    @endif

                    <a href="{{ route('breeding.index', ['a' => $animal->id]) }}" class="mt-3 flex items-center gap-3 rounded-xl bg-rose-50 border border-rose-100 px-4 py-3 hover:bg-rose-100 transition">
                        <span class="text-xl">🧬</span>
                        <span class="flex-1 text-sm font-semibold text-rose-800">{{ __('Find a breeding partner for this animal') }}</span>
                        <span class="text-rose-700">→</span>
                    </a>

                    <div class="mt-6 pt-6 border-t border-stone-100">
                        <div class="flex items-center gap-3">
                            <span class="w-11 h-11 rounded-full bg-stone-900 text-white font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($animal->seller->name, 0, 1)) }}</span>
                            <div>
                                <p class="text-xs text-stone-400">{{ $animal->isBreedingOnly() ? __('Owner') : __('Sold by') }}</p>
                                <p class="font-bold text-stone-900">{{ $animal->seller->name }}</p>
                            </div>
                        </div>

                        @auth
                            @if ($animal->user_id !== auth()->id() && $animal->isForSale())
                                <form method="POST" action="{{ route('animals.messages.start', $animal) }}" class="mt-4">
                                    @csrf
                                    <textarea name="body" rows="2" required class="block w-full border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="{{ __('Hi, is this animal still available?') }}"></textarea>
                                    <x-secondary-button type="submit" class="mt-2 w-full justify-center">💬 {{ __('Message Seller') }}</x-secondary-button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
                    <h2 class="font-bold text-stone-900">{{ __('About this animal') }}</h2>
                    <p class="mt-3 text-stone-600 whitespace-pre-line leading-relaxed">{{ $animal->description ?: __('The seller has not added a description yet.') }}</p>

                    <dl class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ([
                            [__('Breed'), $animal->breed],
                            [__('Age'), $animal->age],
                            [__('Gender'), $animal->gender !== 'unknown' ? __(ucfirst($animal->gender)) : null],
                            [__('Color'), $animal->color],
                            [__('Weight'), $animal->weight],
                            [__('Location'), $animal->location],
                        ] as [$label, $value])
                            <div class="rounded-xl bg-stone-50 p-3">
                                <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ $label }}</dt>
                                <dd class="mt-0.5 font-semibold text-stone-800">{{ $value ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($animal->location)
                        <a href="{{ route('vets.index', array_filter(['state' => $animal->state, 'city' => $animal->location])) }}" class="mt-5 flex items-center gap-3 rounded-xl bg-sky-50 border border-sky-100 px-4 py-3 hover:bg-sky-100 transition">
                            <span class="text-xl">🩺</span>
                            <span class="flex-1 text-sm font-semibold text-sky-800">{{ __('Find animal doctors near :city', ['city' => __($animal->location)]) }}</span>
                            <span class="text-sky-700">→</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="pt-2">
                <h2 class="text-xl font-extrabold text-stone-900 mb-5">{{ $animal->isBreedingOnly() ? __('More breeding animals') : __('Similar animals') }}</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                    @foreach ($related as $item)
                        <x-animal-card :animal="$item" :mode="$animal->isBreedingOnly() ? 'breeding' : 'shop'" :compared="in_array($item->id, session('compare', []))" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
