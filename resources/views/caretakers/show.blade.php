<x-app-layout>
    <x-slot name="title">{{ $caretaker->name }}</x-slot>

    @php
        $tel = preg_replace('/[^0-9+]/', '', $caretaker->phone);
        $wa = $caretaker->whatsappNumber();
        $mapQuery = trim(collect([$caretaker->address, $caretaker->city, $caretaker->state])->filter()->join(', '));
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">
        <nav class="text-sm text-stone-500 flex flex-wrap items-center gap-x-2 gap-y-1">
            <a href="{{ route('home') }}" class="hover:text-amber-700">{{ __('Home') }}</a>
            <span>/</span>
            <a href="{{ route('caretakers.index') }}" class="hover:text-amber-700">{{ __('Caretakers') }}</a>
            <span>/</span>
            <span class="text-stone-800 font-medium truncate">{{ $caretaker->name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-7">
                    <div class="flex items-start gap-4 sm:gap-5">
                        @if ($caretaker->photoUrl())
                            <img src="{{ $caretaker->photoUrl() }}" alt="{{ $caretaker->name }}" class="w-20 h-20 sm:w-28 sm:h-28 rounded-3xl object-cover bg-stone-100 shrink-0">
                        @else
                            <span class="w-20 h-20 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-br from-emerald-100 to-amber-100 text-emerald-800 text-4xl font-extrabold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($caretaker->name, 0, 1)) }}</span>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900">{{ $caretaker->name }}</h1>
                            @if ($caretaker->headline)
                                <p class="text-stone-600 font-semibold">{{ $caretaker->headline }}</p>
                            @endif
                            @if ($caretaker->experience_years)
                                <p class="mt-1 text-sm text-stone-500">{{ $caretaker->experience_years }} {{ __('years experience') }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="px-2.5 py-1 rounded-full bg-stone-100 text-xs font-semibold text-stone-700">📍 {{ __($caretaker->city) }}, {{ __($caretaker->state) }}</span>
                                @if ($caretaker->home_visit)
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700">🏠 {{ __('Comes to you') }}</span>
                                @endif
                                @if ($caretaker->boarding)
                                    <span class="px-2.5 py-1 rounded-full bg-sky-50 border border-sky-100 text-xs font-bold text-sky-700">🛏️ {{ __('Boarding') }}</span>
                                @endif
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $caretaker->available ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">{{ $caretaker->available ? '✅ '.__('Available now') : __('Busy right now') }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($caretaker->about)
                        <p class="mt-6 text-stone-600 leading-relaxed whitespace-pre-line">{{ $caretaker->about }}</p>
                    @endif

                    <dl class="mt-6 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-stone-50 p-3">
                            <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ __('Rate') }}</dt>
                            <dd class="mt-0.5 font-semibold text-stone-800">{{ $caretaker->rateLabel() ?? __('Ask the caretaker') }}</dd>
                        </div>
                        <div class="rounded-xl bg-stone-50 p-3">
                            <dt class="text-[11px] uppercase tracking-wider text-stone-400 font-semibold">{{ __('Area') }}</dt>
                            <dd class="mt-0.5 font-semibold text-stone-800">{{ $caretaker->address ?: __($caretaker->city) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-7 space-y-6">
                    <div>
                        <h2 class="font-bold text-stone-900">{{ __('Animals looked after') }}</h2>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($caretaker->categories as $category)
                                <a href="{{ route('caretakers.index', ['type' => $category->id, 'state' => $caretaker->state, 'city' => $caretaker->city]) }}" class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-full bg-stone-50 border border-stone-200 hover:border-emerald-400 text-sm font-semibold text-stone-700">
                                    <img src="{{ asset('images/animals/'.\Illuminate\Support\Str::slug($category->name).'.svg') }}" alt="" class="w-7 h-7 rounded-full">
                                    {{ __($category->name) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if ($caretaker->serviceList())
                        <div>
                            <h2 class="font-bold text-stone-900">{{ __('Services') }}</h2>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($caretaker->serviceList() as $service)
                                    <span class="px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-sm font-semibold text-emerald-800">{{ $service }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <aside class="lg:sticky lg:top-24 space-y-4">
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
                    <h2 class="font-bold text-stone-900">{{ __('Contact') }} {{ \Illuminate\Support\Str::before($caretaker->name, ' ') }}</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ __('Tell them which animals you have, where, and for how long.') }}</p>
                    <div class="mt-4 space-y-2.5">
                        <a href="tel:{{ $tel }}" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">📞 {{ __('Call') }} {{ $caretaker->phone }}</a>
                        @if ($wa)
                            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 hover:bg-green-100 font-bold">💬 {{ __('WhatsApp') }}</a>
                        @endif
                        @if ($caretaker->user_id && $caretaker->user_id !== auth()->id())
                            @auth
                                <form method="POST" action="{{ route('messages.provider', $caretaker->user_id) }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">✉️ {{ __('Message on AnimalMandi') }}</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">✉️ {{ __('Log in to message') }}</a>
                            @endauth
                        @endif
                        <a href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 py-3 rounded-xl border border-stone-200 hover:border-amber-500 hover:text-amber-700 text-stone-700 font-bold">🧭 {{ __('See on map') }}</a>
                    </div>
                </div>
                <p class="text-xs text-stone-400 px-1">{{ __('Caretakers list their own details. Please agree the work, timing and payment with them directly before you hand over your animals.') }}</p>
            </aside>
        </div>

        @if ($nearby->isNotEmpty())
            <section class="pt-4">
                <h2 class="text-xl font-extrabold text-stone-900 mb-5">{{ __('Other caretakers nearby') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach ($nearby as $other)
                        <x-caretaker-card :caretaker="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
