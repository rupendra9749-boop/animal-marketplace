<x-app-layout>
    <x-slot name="title">{{ __('Find an animal doctor') }}</x-slot>

    <section class="relative overflow-hidden bg-stone-950">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 25%, #0ea5e9 0, transparent 40%), radial-gradient(circle at 85% 70%, #10b981 0, transparent 45%);"></div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-28 text-center">
            <span class="inline-block px-3 py-1 rounded-full bg-sky-500/10 border border-sky-400/30 text-sky-200 text-xs font-semibold">🩺 {{ __('Animal doctors near you') }}</span>
            <h1 class="mt-5 text-3xl sm:text-5xl font-extrabold text-white tracking-tight">{{ __('Find a vet near you') }}</h1>
            <p class="mt-3 text-stone-300 max-w-2xl mx-auto">{{ __('Search animal doctors within :km km of your city by the animal they treat and the service you need. Call or WhatsApp them straight away.', ['km' => \App\Support\Nearby::VET_RADIUS_KM]) }}</p>
            <p class="mt-4 text-sm text-emerald-300 font-semibold">{{ $totalVets }} {{ __('doctors listed across India') }}</p>
        </div>
    </section>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">
        <div class="bg-white rounded-2xl border border-stone-200/70 shadow-xl shadow-stone-200/40 p-4 sm:p-6">
        <div class="mb-4 sm:max-w-sm">@include('layouts.location-chip', ['inline' => true])</div>
        <form method="GET" action="{{ route('vets.index') }}">
            @if (request()->query('city'))
                <input type="hidden" name="state" value="{{ request('state') }}">
                <input type="hidden" name="city" value="{{ request('city') }}">
            @endif

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-5">
                    <label for="v-search" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Doctor, clinic or service') }}</label>
                    <input id="v-search" type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('e.g. vaccination, surgery, AI') }}" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div class="md:col-span-4">
                    <label for="v-type" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Animal treated') }}</label>
                    <select id="v-type" name="type" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="">{{ __('Any animal') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('type') == $category->id)>{{ __($category->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label for="v-radius" class="block text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Distance') }}</label>
                    <select id="v-radius" name="radius" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                        @foreach ($radii as $km)
                            <option value="{{ $km }}" @selected($radius === $km)>{{ __('Within') }} {{ $km }} km</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                    <input type="checkbox" name="emergency" value="1" @checked(request()->boolean('emergency')) class="rounded border-stone-300 text-red-600 focus:ring-red-500"> 🚨 {{ __('24x7 emergency') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                    <input type="checkbox" name="home" value="1" @checked(request()->boolean('home')) class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"> 🏠 {{ __('Home visit') }}
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <x-primary-button class="justify-center px-6 py-3">{{ __('Search') }}</x-primary-button>
                @if ($filtering)
                    <a href="{{ route('vets.index') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Clear filters') }}</a>
                @endif
            </div>
        </form>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <x-flash class="mb-6" />

        @unless ($location)
            <x-location-gate :radius="$radius" what="animal doctors" />
        @else
            <div class="flex items-end justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-stone-900">{{ __('Doctors near :city', ['city' => __($location['city'])]) }}</h2>
                    <p class="text-sm text-stone-500 mt-1">{{ $vets->total() }} {{ __('doctors found') }} &middot; {{ __('within :km km, nearest first', ['km' => $radius]) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @forelse ($vets as $vet)
                    <x-vet-card :vet="$vet" />
                @empty
                    <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-10 sm:p-12 text-center">
                        <p class="text-4xl">🩺</p>
                        <p class="mt-3 font-semibold text-stone-800">{{ __('No doctors within :km km of :city yet.', ['km' => $radius, 'city' => __($location['city'])]) }}</p>
                        <p class="mt-1 text-sm text-stone-500">{{ __('Try a bigger distance, another city, or clear the filters.') }}</p>
                        @if ($aiEnabled)
                            <div class="mt-6 max-w-md mx-auto rounded-2xl bg-violet-50 border border-violet-100 p-4 text-left">
                                <p class="font-bold text-violet-900">🤖 {{ __('Not in our list yet? Search the web with AI') }}</p>
                                <p class="mt-1 text-xs text-violet-800/80">{{ __('AI searches the web for real clinics near :city and saves them here, marked as unverified.', ['city' => __($location['city'])]) }}</p>
                                @auth
                                    <form method="POST" action="{{ route('vets.ai-search') }}" class="mt-3" x-data="{ busy: false }" @submit="busy = true">
                                        @csrf
                                        <input type="hidden" name="state" value="{{ $location['state'] }}">
                                        <input type="hidden" name="city" value="{{ __($location['city']) }}">
                                        <input type="hidden" name="type" value="{{ request('type') }}">
                                        <button type="submit" :disabled="busy" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold disabled:opacity-60">
                                            <span x-text="busy ? @js(__('Searching the web... this can take up to a minute')) : @js(__('Search with AI'))"></span>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="mt-3 block text-center px-4 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold">{{ __('Log in to search with AI') }}</a>
                                @endauth
                            </div>
                        @endif
                        <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
                            @if ($radius < 50)
                                <a href="{{ route('vets.index', array_filter(['radius' => 50, 'type' => request('type'), 'state' => request('state'), 'city' => request('city')])) }}" class="px-4 py-2 rounded-lg bg-sky-50 text-sky-800 hover:bg-sky-100">{{ __('Search within 50 km') }}</a>
                            @endif
                            <a href="{{ route('vets.index') }}" class="px-4 py-2 rounded-lg bg-stone-100 text-stone-700 hover:bg-stone-200">{{ __('Clear filters') }}</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">{{ $vets->links() }}</div>

            @if ($aiEnabled && $vets->total() > 0 && $vets->total() < 6 && auth()->check())
                <form method="POST" action="{{ route('vets.ai-search') }}" class="mt-6 text-center" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <input type="hidden" name="state" value="{{ $location['state'] }}">
                    <input type="hidden" name="city" value="{{ __($location['city']) }}">
                    <input type="hidden" name="type" value="{{ request('type') }}">
                    <button type="submit" :disabled="busy" class="text-sm font-semibold text-violet-700 hover:underline disabled:opacity-60">🤖 <span x-text="busy ? @js(__('Searching the web...')) : @js(__('Want more choice? Search the web with AI'))"></span></button>
                </form>
            @endif
        @endunless

        {{-- Location-wise: every place that has doctors --}}
        @if ($cityCounts->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-sm font-bold uppercase tracking-wider text-stone-500">📍 {{ __('Doctors by city') }}</h2>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($cityCounts as $row)
                        @php $active = $location && $location['city'] === $row->city; @endphp
                        <a href="{{ route('vets.index', array_filter(['state' => $row->state, 'city' => $row->city, 'type' => request('type')])) }}"
                           class="inline-flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-full border text-sm font-semibold transition {{ $active ? 'bg-sky-600 border-sky-600 text-white' : 'bg-white border-stone-200 text-stone-700 hover:border-sky-400 hover:text-sky-700' }}">
                            {{ __($row->city) }}
                            <span class="min-w-[22px] px-1.5 py-0.5 rounded-full text-[11px] font-bold text-center {{ $active ? 'bg-white/25 text-white' : 'bg-stone-100 text-stone-600' }}">{{ $row->total }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-14 rounded-3xl bg-gradient-to-r from-sky-600 to-emerald-600 px-6 py-9 sm:px-12 flex flex-col md:flex-row items-center justify-between gap-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Are you an animal doctor?') }}</h2>
                <p class="mt-1 text-sky-100 max-w-lg">{{ __('List your clinic for free so animal owners near you can find and call you.') }}</p>
            </div>
            <a href="{{ route('vets.create') }}" class="shrink-0 bg-white text-sky-700 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-sky-50 transition">{{ __('List your clinic →') }}</a>
        </div>
    </section>
</x-app-layout>
