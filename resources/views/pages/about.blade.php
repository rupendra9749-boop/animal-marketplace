<x-app-layout>
    <section class="bg-stone-950 relative overflow-hidden">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 80% 30%, #d97706 0, transparent 45%);"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <span class="text-amber-400 text-sm font-bold uppercase tracking-wider">{{ __('About us') }}</span>
            <h1 class="mt-3 text-4xl sm:text-5xl font-extrabold text-white tracking-tight">{{ __('Making animal trade simple, fair and local.') }}</h1>
            <p class="mt-5 text-lg text-stone-300">{{ config('app.name') }} {{ __('connects farmers, breeders and pet lovers across India so healthy animals find the right homes — without middlemen.') }}</p>
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative">
        <div class="grid grid-cols-3 gap-4 bg-white rounded-2xl border border-stone-200/70 shadow-xl p-6 text-center">
            <div>
                <p class="text-3xl font-extrabold text-stone-900">{{ $stats['animals'] }}+</p>
                <p class="text-sm text-stone-500">{{ __('Animals listed') }}</p>
            </div>
            <div class="border-x border-stone-100">
                <p class="text-3xl font-extrabold text-stone-900">{{ $stats['sellers'] }}+</p>
                <p class="text-sm text-stone-500">{{ __('Trusted sellers') }}</p>
            </div>
            <div>
                <p class="text-3xl font-extrabold text-stone-900">{{ $stats['cities'] }}+</p>
                <p class="text-sm text-stone-500">{{ __('Cities covered') }}</p>
            </div>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl font-extrabold text-stone-900">{{ __('Our story') }}</h2>
            <p class="mt-4 text-stone-600 leading-relaxed">
                {{ __('Buying a cow, buffalo or even a puppy used to mean travelling to crowded mandis, trusting word of mouth, and hoping for the best. We built this platform so buyers can see every detail up front — breed, age, weight, vaccination — and talk directly to the seller before making a decision.') }}
            </p>
            <p class="mt-4 text-stone-600 leading-relaxed">
                {{ __('Sellers get a free, simple way to reach serious buyers within 250 km, and buyers can compare animals side by side to pick the best one for their needs and budget.') }}
            </p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            @foreach (['buffalo', 'goat', 'horse', 'sheep'] as $i => $slug)
                <img src="{{ asset("images/animals/{$slug}.svg") }}" alt="" class="rounded-3xl w-full aspect-square object-cover border border-stone-200/70 {{ $i % 2 ? 'mt-8' : '' }}">
            @endforeach
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
        <h2 class="text-3xl font-extrabold text-stone-900 text-center">{{ __('What we stand for') }}</h2>
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach ([
                ['🤝', __('Transparency'), __('Every listing shows full details, and buyers can chat with sellers before paying.')],
                ['🩺', __('Animal welfare'), __('We encourage vaccinated, healthy animals and clearly highlight vaccination status.')],
                ['📍', __('Local first'), __('Find animals near you to reduce travel stress for both animals and people.')],
            ] as [$icon, $title, $text])
                <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-2xl flex items-center justify-center">{{ $icon }}</div>
                    <h3 class="mt-4 font-bold text-stone-900">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-stone-500 leading-relaxed">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20">
        <div class="rounded-3xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-12 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">{{ __('Questions? We\'d love to help.') }}</h2>
            <a href="{{ route('contact') }}" class="inline-block mt-6 bg-white text-amber-700 font-bold px-6 py-3 rounded-xl hover:bg-amber-50">{{ __('Contact us') }}</a>
        </div>
    </section>
</x-app-layout>
