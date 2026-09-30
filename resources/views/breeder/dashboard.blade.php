<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Breeder Dashboard') }}</h2></x-slot>

    <div class="rounded-2xl bg-gradient-to-r from-rose-900 to-stone-900 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-rose-200/70 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('Welcome back,') }} {{ auth()->user()->name }} 🧬</h1>
            <p class="text-rose-100/70 text-sm mt-1">{{ __('List your studs and dams, and owners within 250 km can request breeding.') }}</p>
        </div>
        <a href="{{ route('breeder.animals.create') }}" class="shrink-0 bg-white text-rose-800 font-bold text-sm px-5 py-3 rounded-xl text-center hover:bg-rose-50">+ {{ __('Add breeding animal') }}</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            [__('Breeding animals'), $stats['animals'], $stats['active'].' '.__('visible to owners'), 'bg-rose-500', route('breeder.animals.index'), '🧬'],
            [__('Studs (male)'), $stats['studs'], __('offered for breeding'), 'bg-sky-500', route('breeder.animals.index'), '♂'],
            [__('Dams (female)'), $stats['dams'], __('offered for breeding'), 'bg-amber-500', route('breeder.animals.index'), '♀'],
            [__('Unread chats'), $stats['unread'], __('breeding requests'), 'bg-violet-500', route('messages.index'), '💬'],
        ] as [$label, $value, $sub, $color, $href, $glyph])
            <a href="{{ $href }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-500">{{ $label }}</p>
                    <span class="w-10 h-10 rounded-xl {{ $color }} text-white flex items-center justify-center shadow-sm text-lg font-bold">{{ $glyph }}</span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $value }}</p>
                <p class="text-xs text-stone-500 mt-1">{{ $sub }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
                <h3 class="font-bold text-stone-900">{{ __('My breeding animals') }}</h3>
                <a href="{{ route('breeder.animals.index') }}" class="text-sm font-semibold text-rose-700 hover:underline">{{ __('View all') }}</a>
            </div>
            @forelse ($latest as $animal)
                <a href="{{ route('breeder.animals.show', $animal) }}" class="flex items-center gap-4 px-6 py-3.5 border-b border-stone-100 last:border-0 hover:bg-stone-50">
                    <img src="{{ $animal->imageUrl() }}" class="w-12 h-12 rounded-xl object-cover bg-stone-100" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-stone-900 truncate">{{ $animal->name }}</p>
                        <p class="text-xs text-stone-500 truncate">{{ collect([__($animal->category?->name), $animal->gender === 'male' ? __('Stud') : ($animal->gender === 'female' ? __('Dam') : null), __($animal->location)])->filter()->join(' · ') }}</p>
                    </div>
                    <span class="font-extrabold text-stone-900">{{ (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee) : '—' }}</span>
                </a>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-4xl">🧬</p>
                    <p class="mt-3 font-semibold text-stone-700">{{ __('No breeding animals yet') }}</p>
                    <p class="text-sm text-stone-500">{{ __('Add your first stud or dam and set your breeding fee.') }}</p>
                    <a href="{{ route('breeder.animals.create') }}" class="inline-block mt-4"><x-primary-button type="button">+ {{ __('Add breeding animal') }}</x-primary-button></a>
                </div>
            @endforelse
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/70 p-6">
            <h3 class="font-bold text-stone-900">{{ __('How it works') }}</h3>
            <ol class="mt-4 space-y-4 text-sm text-stone-600">
                <li class="flex gap-3"><span class="w-7 h-7 rounded-full bg-rose-50 text-rose-700 font-bold flex items-center justify-center shrink-0">1</span>{{ __('You list a healthy stud or dam with your fee and city.') }}</li>
                <li class="flex gap-3"><span class="w-7 h-7 rounded-full bg-rose-50 text-rose-700 font-bold flex items-center justify-center shrink-0">2</span>{{ __('Owners within 250 km find it, check the match and send a request.') }}</li>
                <li class="flex gap-3"><span class="w-7 h-7 rounded-full bg-rose-50 text-rose-700 font-bold flex items-center justify-center shrink-0">3</span>{{ __('You agree date, place and fee in Chats. We never take a cut.') }}</li>
            </ol>
            <div class="mt-5 grid grid-cols-2 gap-3 text-sm font-semibold">
                <a href="{{ route('breeder.animals.create') }}" class="rounded-xl bg-rose-50 text-rose-800 px-3 py-3 text-center hover:bg-rose-100">+ {{ __('Add animal') }}</a>
                <a href="{{ route('breeding.browse') }}" class="rounded-xl bg-stone-100 text-stone-700 px-3 py-3 text-center hover:bg-stone-200">{{ __('See public page') }}</a>
            </div>
        </div>
    </div>
</x-panel-layout>
