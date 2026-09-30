<x-app-layout>
    <x-slot name="title">{{ __('Find an animal caretaker') }}</x-slot>

    <section class="relative overflow-hidden bg-stone-950">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 25%, #10b981 0, transparent 40%), radial-gradient(circle at 85% 70%, #f59e0b 0, transparent 45%);"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-28 text-center">
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-400/30 text-emerald-200 text-xs font-semibold">🤝 {{ __('Animal caretakers near you') }}</span>
            <h1 class="mt-5 text-3xl sm:text-5xl font-extrabold text-white tracking-tight">{{ __('Someone to look after your animals') }}</h1>
            <p class="mt-3 text-stone-300 max-w-2xl mx-auto">{{ __('Find trusted caretakers within :km km of you for daily feeding and care, milking, grooming, boarding and farm help.', ['km' => \App\Support\Nearby::CARETAKER_RADIUS_KM]) }}</p>
            <p class="mt-4 text-sm text-amber-300 font-semibold">{{ $total }} {{ __('caretakers listed across India') }}</p>
        </div>
    </section>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">
        <div class="bg-white rounded-2xl border border-stone-200/70 shadow-xl shadow-stone-200/40 p-4 sm:p-6">
            <div class="mb-4 sm:max-w-sm">@include('layouts.location-chip', ['inline' => true])</div>
            <form method="GET" action="{{ route('caretakers.index') }}">
                @if (request()->query('city'))
                    <input type="hidden" name="state" value="{{ request('state') }}">
                    <input type="hidden" name="city" value="{{ request('city') }}">
                @endif

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-5">
                        <label for="c-search" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Name or service') }}</label>
                        <input id="c-search" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('e.g. milking, feeding, boarding') }}" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                    </div>
                    <div class="md:col-span-4">
                        <label for="c-type" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Animal') }}</label>
                        <select id="c-type" name="type" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">{{ __('Any animal') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('type') == $category->id)>{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label for="c-radius" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Distance') }}</label>
                        <select id="c-radius" name="radius" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                            @foreach ($radii as $km)
                                <option value="{{ $km }}" @selected($radius === $km)>{{ __('Within') }} {{ $km }} km</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                        <input type="checkbox" name="home" value="1" @checked(request()->boolean('home')) class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"> 🏠 {{ __('Comes to you') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                        <input type="checkbox" name="boarding" value="1" @checked(request()->boolean('boarding')) class="rounded border-stone-300 text-sky-600 focus:ring-sky-500"> 🛏️ {{ __('Boarding') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                        <input type="checkbox" name="available" value="1" @checked(request()->boolean('available')) class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"> ✅ {{ __('Available now') }}
                    </label>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <x-primary-button class="justify-center px-6 py-3">{{ __('Search') }}</x-primary-button>
                    @if ($filtering)
                        <a href="{{ route('caretakers.index') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Clear filters') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <x-flash class="mb-6" />

        @unless ($location)
            <x-location-gate :radius="$radius" what="caretakers" />
        @else
            <div class="mb-6">
                <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900">{{ __('Caretakers near :city', ['city' => __($location['city'])]) }}</h2>
                <p class="text-sm text-stone-500 mt-1">{{ $caretakers->total() }} {{ __('caretakers found') }} &middot; {{ __('within :km km, nearest first', ['km' => $radius]) }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @forelse ($caretakers as $caretaker)
                    <x-caretaker-card :caretaker="$caretaker" />
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-10 sm:p-12 text-center">
                        <p class="text-4xl">🤝</p>
                        <p class="mt-3 font-semibold text-stone-800">{{ __('No caretakers within :km km of :city yet.', ['km' => $radius, 'city' => __($location['city'])]) }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ __('Try a bigger distance, another city, or clear the filters.') }}</p>
                        <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
                            @if ($radius < 50)
                                <a href="{{ route('caretakers.index', array_filter(['radius' => 50, 'type' => request('type')])) }}" class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100">{{ __('Search within 50 km') }}</a>
                            @endif
                            <a href="{{ route('caretakers.index') }}" class="px-4 py-2 rounded-lg bg-stone-100 text-stone-700 hover:bg-stone-200">{{ __('Clear filters') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $caretakers->links() }}</div>
        @endunless

        <div class="mt-14 rounded-3xl bg-gradient-to-r from-emerald-600 to-amber-600 px-6 py-9 sm:px-12 flex flex-col md:flex-row items-center justify-between gap-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Do you look after animals for a living?') }}</h2>
                <p class="mt-1 text-emerald-50 max-w-lg">{{ __('Create a free profile and get calls from animal owners near you.') }}</p>
            </div>
            <a href="{{ route('caretakers.create') }}" class="shrink-0 bg-white text-emerald-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-emerald-50 transition">{{ __('Offer your services →') }}</a>
        </div>
    </section>
</x-app-layout>
