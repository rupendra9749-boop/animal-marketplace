<x-app-layout>
    <x-slot name="title">{{ $vet->name }}</x-slot>

    @php
        $tel = preg_replace('/[^0-9+]/', '', $vet->phone);
        $wa = $vet->whatsappNumber();
        $mapQuery = trim(collect([$vet->clinic_name, $vet->address, $vet->city])->filter()->join(', '));
        $initial = mb_strtoupper(mb_substr(preg_replace('/^(dr\.?\s*)/i', '', $vet->name), 0, 1));
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
        <nav class="text-sm text-stone-500 flex flex-wrap items-center gap-x-2 gap-y-1">
            <a href="{{ route('home') }}" class="hover:text-amber-700">{{ __('Home') }}</a>
            <span>/</span>
            <a href="{{ route('vets.index') }}" class="hover:text-amber-700">{{ __('Animal doctors') }}</a>
            <span>/</span>
            <a href="{{ route('vets.index', ['state' => $vet->state, 'city' => $vet->city, 'radius' => 25]) }}" class="hover:text-amber-700">{{ __($vet->city) }}</a>
            <span>/</span>
            <span class="text-stone-800 font-medium truncate">{{ $vet->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
                {{-- Profile header --}}
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-7">
                    <div class="flex items-start gap-4 sm:gap-5">
                        @if ($vet->photoUrl())
                            <img src="{{ $vet->photoUrl() }}" alt="{{ $vet->name }}" class="w-20 h-20 sm:w-28 sm:h-28 rounded-3xl object-cover bg-stone-100 shrink-0">
                        @else
                            <span class="w-20 h-20 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-br from-sky-100 to-emerald-100 text-sky-800 text-4xl font-extrabold flex items-center justify-center shrink-0">{{ $initial }}</span>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">{{ $vet->name }}</h1>
                            @if ($vet->clinic_name)
                                <p class="text-stone-600 font-semibold">{{ $vet->clinic_name }}</p>
                            @endif
                            <p class="mt-1 text-sm text-stone-500">{{ collect([$vet->qualification, $vet->experience_years ? $vet->experience_years.' '.__('years experience') : null])->filter()->join(' · ') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="px-2.5 py-1 rounded-full bg-stone-100 text-xs font-semibold text-stone-700">📍 {{ __($vet->city) }}</span>
                                @if ($vet->emergency)
                                    <span class="px-2.5 py-1 rounded-full bg-red-50 border border-red-100 text-xs font-bold text-red-700">🚨 {{ __('24x7 emergency') }}</span>
                                @endif
                                @if ($vet->home_visit)
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">🏠 {{ __('Home visit') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($vet->isAiFound() && ! $vet->is_verified)
                        <div class="mt-6 rounded-xl bg-violet-50 border border-violet-100 px-4 py-3 text-sm text-violet-900">
                            🤖 <strong>{{ __('Found by AI web search - not verified yet.') }}</strong>
                            {{ __('Please call to confirm the details before you travel.') }}
                            @if ($vet->source_url)
                                <a href="{{ $vet->source_url }}" target="_blank" rel="noopener nofollow" class="font-semibold underline">{{ __('Source: :site', ['site' => $vet->sourceHost()]) }}</a>
                            @endif
                        </div>
                    @endif

                    @if ($vet->about)
                        <p class="mt-6 text-stone-600 leading-relaxed whitespace-pre-line">{{ $vet->about }}</p>
                    @endif

                    <dl class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-stone-50 p-3">
                            <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ __('Consultation') }}</dt>
                            <dd class="mt-0.5 font-semibold text-stone-800">{{ $vet->consultation_fee === null ? __('Ask the clinic') : ((float) $vet->consultation_fee > 0 ? inr($vet->consultation_fee, 0) : __('Free')) }}</dd>
                        </div>
                        <div class="rounded-xl bg-stone-50 p-3">
                            <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ __('Timings') }}</dt>
                            <dd class="mt-0.5 font-semibold text-stone-800">{{ $vet->timings ?: '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Treats + services --}}
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-7 space-y-6">
                    <div>
                        <h2 class="font-bold text-stone-900">{{ __('Animals treated') }}</h2>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($vet->categories as $category)
                                <a href="{{ route('vets.index', ['type' => $category->id, 'state' => $vet->state, 'city' => $vet->city]) }}" class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-full bg-stone-50 border border-stone-200 hover:border-sky-400 text-sm font-semibold text-stone-700">
                                    <img src="{{ asset('images/animals/'.\Illuminate\Support\Str::slug($category->name).'.svg') }}" alt="" class="w-7 h-7 rounded-full">
                                    {{ __($category->name) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if ($vet->serviceList())
                        <div>
                            <h2 class="font-bold text-stone-900">{{ __('Services') }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($vet->serviceList() as $service)
                                    <span class="px-3 py-1.5 rounded-full bg-sky-50 border border-sky-100 text-sm font-semibold text-sky-800">{{ $service }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Location --}}
                <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
                    <div class="p-5 sm:p-7">
                        <h2 class="font-bold text-stone-900">{{ __('Location') }}</h2>
                        <p class="mt-2 text-stone-600">{{ collect([$vet->address, __($vet->city)])->filter()->join(', ') }}</p>
                    </div>
                    @if ($vet->latitude !== null)
                        <iframe title="{{ __('Map') }}" loading="lazy" class="block w-full h-64 border-0 border-t border-stone-100"
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $vet->longitude - 0.06 }}%2C{{ $vet->latitude - 0.04 }}%2C{{ $vet->longitude + 0.06 }}%2C{{ $vet->latitude + 0.04 }}&amp;layer=mapnik&amp;marker={{ $vet->latitude }}%2C{{ $vet->longitude }}"></iframe>
                        <p class="px-5 sm:px-7 py-3 text-xs text-stone-400">{{ __('Map shows the approximate area of :city.', ['city' => __($vet->city)]) }}</p>
                    @endif
                </div>
            </div>

            {{-- Contact --}}
            <aside class="lg:sticky lg:top-24 space-y-4">
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
                    <h2 class="font-bold text-stone-900">{{ __('Contact this doctor') }}</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ __('Tell them what animal you have and what is wrong.') }}</p>
                    <div class="mt-4 space-y-2.5">
                        <a href="tel:{{ $tel }}" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">📞 {{ __('Call') }} {{ $vet->phone }}</a>
                        @if ($wa)
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 hover:bg-green-100 font-bold">💬 {{ __('WhatsApp') }}</a>
                        @endif
                        @if ($vet->user_id && $vet->user_id !== auth()->id())
                            @auth
                                <form method="POST" action="{{ route('messages.provider', $vet->user_id) }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">✉️ {{ __('Message on AnimalMandi') }}</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">✉️ {{ __('Log in to message') }}</a>
                            @endauth
                        @endif
                        <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">🧭 {{ __('Get directions') }}</a>
                        @if ($vet->email)
                            <a href="mailto:{{ $vet->email }}" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">✉️ {{ __('Email') }}</a>
                        @endif
                    </div>
                    @if ($vet->emergency)
                        <p class="mt-4 rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs font-semibold text-red-700">🚨 {{ __('Available for emergencies, day and night.') }}</p>
                    @endif
                </div>
                <p class="text-xs text-stone-400 px-1">{{ __('Doctors list their own details. Please confirm timings and fees on the phone before you travel.') }}</p>
            </aside>
        </div>

        @if ($nearby->isNotEmpty())
            <section class="pt-4">
                <h2 class="text-xl font-extrabold text-stone-900 mb-5">{{ __('Other doctors nearby') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($nearby as $other)
                        <x-vet-card :vet="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
