<x-app-layout>
    <x-slot name="title">{{ __('Breeding animals') }}</x-slot>

    <section class="relative overflow-hidden bg-stone-950">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 20%, #d97706 0, transparent 40%), radial-gradient(circle at 85% 70%, #be185d 0, transparent 45%);"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-28 text-center">
            <x-breeding-tabs active="browse" />
            <h1 class="mt-6 text-3xl sm:text-5xl font-extrabold text-white tracking-tight">{{ __('Breeding animals') }}</h1>
            <p class="mt-3 text-stone-300 max-w-2xl mx-auto">{{ __('Healthy studs and dams offered for breeding - find one near you, check the match, then message the owner.') }}</p>
            <p class="mt-4 text-sm text-amber-300 font-semibold">{{ $location ? __(':n animals for breeding within :km km of :city', ['n' => $total, 'km' => $radius, 'city' => __($location['city'])]) : __(':n animals available for breeding', ['n' => $total]) }}</p>
        </div>
    </section>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">
        <div class="bg-white rounded-2xl border border-stone-200/70 shadow-xl shadow-stone-200/40 p-4 sm:p-6">
        <div class="mb-4 sm:max-w-sm">@include('layouts.location-chip', ['inline' => true])</div>
        <form method="GET" action="{{ route('breeding.browse') }}">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <label for="b-search" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Breed or name') }}</label>
                    <input id="b-search" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('e.g. Labrador, Murrah') }}" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div class="md:col-span-4">
                    <label for="b-type" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Animal') }}</label>
                    <select id="b-type" name="type" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">{{ __('All animals') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('type') == $category->id)>{{ __($category->name) }} ({{ $category->animals_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label for="b-sex" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Sex') }}</label>
                    <select id="b-sex" name="sex" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">{{ __('Male or female') }}</option>
                        <option value="male" @selected($sex === 'male')>♂ {{ __('Male (stud)') }}</option>
                        <option value="female" @selected($sex === 'female')>♀ {{ __('Female (dam)') }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <x-primary-button class="justify-center px-6 py-3">{{ __('Search') }}</x-primary-button>
                @if ($filtering)
                    <a href="{{ route('breeding.browse') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Clear filters') }}</a>
                @endif
            </div>
        </form>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
        <x-flash class="mb-6" />

        <div class="flex items-end justify-between mb-6 gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900">{{ $filtering ? __('Search results') : __('Available for breeding') }}</h2>
                <p class="text-sm text-stone-500 mt-1">{{ $animals->total() }} {{ __('animals found') }} &middot; {{ __('within :km km, nearest first', ['km' => $radius]) }}</p>
            </div>
            <a href="{{ route('breeding.index') }}" class="hidden sm:inline-flex text-sm font-semibold text-rose-700 hover:underline">🧬 {{ __('Check compatibility of two animals →') }}</a>
        </div>

        @unless ($location)
            <x-location-gate :radius="$radius" what="breeding animals" />
        @else
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            @forelse ($animals as $animal)
                <x-animal-card :animal="$animal" mode="breeding" :wishlisted="$wishlisted->contains($animal->id)" />
            @empty
                <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-10 sm:p-12 text-center">
                    <p class="text-4xl">🐾</p>
                    <p class="mt-3 font-semibold text-stone-800">{{ $filtering ? __('No breeding animals match your search.') : __('No animals are listed for breeding yet.') }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ __('Nothing within :km km of you right now. Try another animal or a different city.', ['km' => $radius]) }}</p>
                    @if ($filtering)
                        <a href="{{ route('breeding.browse') }}" class="mt-3 inline-block text-sm font-semibold text-amber-700 hover:underline">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $animals->links() }}</div>
        @endunless
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ([
                ['🔍', __('1. Find'), __('Filter by animal, sex and how far you are willing to travel.')],
                ['🧬', __('2. Check the match'), __('Pick your own animal and the stud or dam - we score age, health, size and distance.')],
                ['💬', __('3. Message the owner'), __('Agree the fee, date and place directly. We never take a cut.')],
            ] as [$icon, $title, $text])
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 flex gap-4">
                    <span class="w-11 h-11 rounded-xl bg-rose-50 text-2xl flex items-center justify-center shrink-0">{{ $icon }}</span>
                    <div>
                        <h3 class="font-bold text-stone-900">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-stone-500 leading-relaxed">{{ $text }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 rounded-3xl bg-gradient-to-r from-rose-600 to-amber-600 px-6 py-9 sm:px-12 flex flex-col md:flex-row items-center justify-between gap-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Own a healthy stud or dam?') }}</h2>
                <p class="mt-1 text-rose-100 max-w-lg">{{ __('List it for breeding, set your fee and get enquiries from owners nearby.') }}</p>
            </div>
            <a href="{{ auth()->check() && auth()->user()->isSeller() ? route('seller.animals.create') : route('register') }}" class="shrink-0 bg-white text-rose-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-rose-50 transition">{{ __('List for breeding →') }}</a>
        </div>
    </section>
</x-app-layout>
