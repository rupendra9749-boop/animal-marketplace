<x-app-layout>
    @unless ($filtering)
        {{-- Hero --}}
        <section class="relative overflow-hidden bg-stone-950">
            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, #d97706 0, transparent 40%), radial-gradient(circle at 80% 60%, #92400e 0, transparent 45%);"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold">
                        🐾 {{ __('Trusted by farmers & pet lovers') }}
                    </span>
                    <h1 class="mt-5 text-4xl sm:text-5xl font-extrabold text-white tracking-tight leading-[1.1]">
                        {{ __('Buy & sell healthy animals') }}
                        <span class="text-amber-400">{{ __('near you.') }}</span>
                    </h1>
                    <p class="mt-5 text-lg text-stone-300 max-w-xl">
                        {{ __('Cows, buffaloes, goats, dogs, birds and more — compare listings side by side, chat with sellers, and buy with confidence.') }}
                    </p>

                    <form method="GET" action="{{ route('home') }}" class="mt-8 bg-white rounded-2xl shadow-2xl p-2 flex flex-col sm:flex-row gap-2">
                        <div class="flex items-center flex-1 px-3">
                            <svg class="w-5 h-5 text-stone-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                            <input type="text" name="search" placeholder="{{ __('Search breed or animal...') }}" class="w-full border-0 focus:ring-0 text-sm">
                        </div>
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition">
                            {{ __('Search') }}
                        </button>
                    </form>

                    <div class="mt-3 max-w-sm">@include('layouts.location-chip', ['inline' => true])</div>
                    <p class="mt-2 text-xs text-stone-400">{{ __('Showing animals within :km km of your city.', ['km' => $radius]) }}</p>

                    <dl class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                        <div>
                            <dt class="text-xs text-stone-400">{{ __('Live listings') }}</dt>
                            <dd class="text-2xl font-extrabold text-white">{{ $stats['animals'] }}+</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-400">{{ __('Sellers') }}</dt>
                            <dd class="text-2xl font-extrabold text-white">{{ $stats['sellers'] }}+</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-400">{{ __('Cities') }}</dt>
                            <dd class="text-2xl font-extrabold text-white">{{ $stats['cities'] }}+</dd>
                        </div>
                    </dl>
                </div>

                <div class="hidden lg:grid grid-cols-2 gap-4">
                    @foreach (['cow', 'dog', 'buffalo', 'bird'] as $i => $slug)
                        <div class="rounded-3xl overflow-hidden ring-1 ring-white/10 shadow-2xl {{ $i % 2 ? 'translate-y-8' : '' }}">
                            <img src="{{ asset("images/animals/{$slug}.svg") }}" alt="" class="w-full aspect-square object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- What else you can do here --}}
        <section class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
                @foreach ([
                    [route('home').'#listings', '🐄', __('Buy animals'), __('Fresh listings near you'), 'bg-amber-50 text-amber-700'],
                    [route('breeding.browse'), '🧬', __('Breeding'), __('Studs and dams for breeding'), 'bg-rose-50 text-rose-700'],
                    [route('vets.index'), '🩺', __('Find a vet'), __('Animal doctors near you'), 'bg-sky-50 text-sky-700'],
                    [route('caretakers.index'), '🤝', __('Caretakers'), __('Daily care and boarding'), 'bg-emerald-50 text-emerald-700'],
                    [route('compare.index'), '⚖️', __('Compare'), __('Pick the best of two'), 'bg-violet-50 text-violet-700'],
                ] as [$href, $icon, $title, $text, $tint])
                    <a href="{{ $href }}" class="group {{ $loop->last ? 'col-span-2 lg:col-span-1' : '' }} bg-white rounded-2xl border border-stone-200/70 shadow-lg shadow-stone-200/50 p-4 sm:p-5 hover:-translate-y-0.5 hover:shadow-xl transition">
                        <span class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl {{ $tint }} text-2xl flex items-center justify-center">{{ $icon }}</span>
                        <p class="mt-3 font-extrabold text-stone-900">{{ $title }}</p>
                        <p class="text-xs sm:text-sm text-stone-500 mt-0.5">{{ $text }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Categories --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-stone-900">{{ __('Browse by animal') }}</h2>
                    <p class="text-sm text-stone-500 mt-1">{{ __('Pick a category to see what\'s available.') }}</p>
                </div>
            </div>
            <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:pb-0 sm:overflow-visible sm:grid sm:grid-cols-4 lg:grid-cols-8">
                @foreach ($categories as $category)
                    <a href="{{ route('home', ['category' => $category->id]) }}" class="group shrink-0 w-28 sm:w-auto bg-white rounded-2xl border border-stone-200/70 p-3 text-center hover:border-amber-400 hover:shadow-lg transition">
                        <img src="{{ asset('images/animals/'.\Illuminate\Support\Str::slug($category->name).'.svg') }}" alt="" class="w-16 h-16 mx-auto rounded-xl group-hover:scale-110 transition">
                        <p class="mt-2 text-sm font-bold text-stone-800">{{ __($category->name) }}</p>
                        <p class="text-xs text-stone-500">{{ $category->animals_count }} {{ __('listed') }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endunless

    {{-- Listings --}}
    <section id="listings" class="scroll-mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 {{ $filtering ? 'pt-8' : 'pt-14' }}">
        <x-flash class="mb-6" />

        @if ($filtering)
            <form method="GET" action="{{ route('home') }}" class="bg-white rounded-2xl border border-stone-200/70 p-3 flex flex-wrap gap-2 mb-6">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search breed or animal...') }}" class="flex-1 min-w-[180px] border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                <select name="category" class="border-stone-200 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">{{ __('All types') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ __($category->name) }}</option>
                    @endforeach
                </select>
                <button class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold px-5 rounded-xl">{{ __('Apply') }}</button>
                <a href="{{ route('home') }}" class="px-4 py-2 text-sm font-semibold text-stone-500 hover:text-stone-800 self-center">{{ __('Clear') }}</a>
            </form>
        @endif

        @unless ($location)
            <x-location-gate :radius="$radius" what="animals" />
        @else
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-2xl font-extrabold text-stone-900">{{ $filtering ? __('Search results') : __('Animals near :city', ['city' => __($location['city'])]) }}</h2>
                <p class="text-sm text-stone-500 mt-1">{{ $animals->total() }} {{ __('animals within :km km, nearest first', ['km' => $radius]) }}</p>
            </div>
            <a href="{{ route('compare.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-amber-700 hover:underline">{{ __('Compare selected →') }}</a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            @forelse ($animals as $animal)
                <x-animal-card :animal="$animal" :wishlisted="$wishlisted->contains($animal->id)" :compared="$compared->contains($animal->id)" />
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-12 text-center">
                    <p class="text-4xl">🔍</p>
                    <p class="mt-3 font-semibold text-stone-800">{{ $filtering ? __('No animals match your search.') : __('No animals for sale within :km km of :city yet.', ['km' => $radius, 'city' => __($location['city'])]) }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ __('Try another city, or check back soon - new animals are added every day.') }}</p>
                    @if ($filtering)
                        <a href="{{ route('home') }}" class="mt-2 inline-block text-sm text-amber-700 hover:underline">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $animals->links() }}</div>
        @endunless
    </section>

    @unless ($filtering)
        {{-- How it works --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
            <div class="text-center max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900">{{ __('How it works') }}</h2>
                <p class="text-stone-500 mt-2">{{ __('From search to purchase in four simple steps.') }}</p>
            </div>
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([
                    ['🔍', __('Search nearby'), __('Filter by animal type and find listings within 250 km of your city.')],
                    ['⚖️', __('Compare'), __('Add up to 4 animals and compare price, breed, age and health side by side.')],
                    ['💬', __('Chat with seller'), __('Ask questions directly before you decide.')],
                    ['✅', __('Buy with confidence'), __('Place your order and track it from your dashboard.')],
                ] as [$icon, $title, $text])
                    <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-2xl flex items-center justify-center">{{ $icon }}</div>
                        <h3 class="mt-4 font-bold text-stone-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-stone-500 leading-relaxed">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Seller CTA --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
            <div class="rounded-3xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-12 sm:px-14 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">{{ __('Have animals to sell?') }}</h2>
                    <p class="mt-2 text-amber-100 max-w-lg">{{ __('List for free in minutes and reach thousands of buyers in your area.') }}</p>
                </div>
                <a href="{{ auth()->check() && auth()->user()->isSeller() ? route('seller.animals.create') : route('register') }}"
                   class="shrink-0 bg-white text-amber-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-amber-50 transition">
                    {{ __('Start selling →') }}
                </a>
            </div>
        </section>
    @endunless
</x-app-layout>
