<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Seller Dashboard') }}</h2></x-slot>

    <div class="rounded-2xl bg-gradient-to-r from-stone-900 to-stone-800 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-stone-400 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('Welcome back,') }} {{ auth()->user()->name }} 👋</h1>
            <p class="text-stone-400 text-sm mt-1">{{ __('Here\'s how your listings are doing.') }}</p>
        </div>
        <a href="{{ route('seller.animals.create') }}" class="shrink-0 bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm px-5 py-3 rounded-xl text-center">+ {{ __('Add new animal') }}</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            [__('Revenue'), inr($stats['revenue'], 0), trans_choice(':count order received|:count orders received', $stats['orders']), 'bg-emerald-500', route('seller.sales.index'), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            [__('My animals'), $stats['animals'], $stats['active_animals'].' '.__('visible to buyers'), 'bg-sky-500', route('seller.animals.index'), 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            [__('Waiting for you'), $stats['waiting'], $stats['waiting'] ? __('orders to approve') : __('nothing to approve'), 'bg-amber-500', route('seller.sales.index'), 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            [__('Unread chats'), $stats['unread'], __('from buyers'), 'bg-violet-500', route('messages.index'), 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ] as [$label, $value, $sub, $color, $href, $path])
            <a href="{{ $href }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-500">{{ $label }}</p>
                    <span class="w-10 h-10 rounded-xl {{ $color }} text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $value }}</p>
                <p class="text-xs text-stone-500 mt-1">{{ $sub }}</p>
            </a>
        @endforeach
    </div>

    {{-- One account, four ways to earn: selling, breeding, doctor and caretaker --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('breeder.animals.index') }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition flex items-start gap-4">
            <span class="w-11 h-11 rounded-xl bg-rose-50 text-2xl flex items-center justify-center shrink-0">🧬</span>
            <div class="min-w-0">
                <p class="font-bold text-stone-900">{{ __('Breeding') }}</p>
                <p class="text-sm text-stone-500">{{ $stats['breeding'] }} {{ __('animals offered for breeding') }}</p>
                <p class="mt-1 text-sm font-semibold text-rose-700">{{ $stats['breeding'] ? __('Manage') : __('Add your first') }} →</p>
            </div>
        </a>
        <a href="{{ route('doctor.profile') }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition flex items-start gap-4">
            <span class="w-11 h-11 rounded-xl bg-sky-50 text-2xl flex items-center justify-center shrink-0">🩺</span>
            <div class="min-w-0">
                <p class="font-bold text-stone-900">{{ __('Doctor profile') }}</p>
                <p class="text-sm text-stone-500">{{ ! $vet ? __('Not created yet') : ($vet->is_active ? __('Live in the doctor search') : __('Hidden by an admin')) }}</p>
                <p class="mt-1 text-sm font-semibold text-sky-700">{{ $vet ? __('Edit') : __('Create it') }} →</p>
            </div>
        </a>
        <a href="{{ route('caretaker.profile') }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition flex items-start gap-4">
            <span class="w-11 h-11 rounded-xl bg-emerald-50 text-2xl flex items-center justify-center shrink-0">🤝</span>
            <div class="min-w-0">
                <p class="font-bold text-stone-900">{{ __('Caretaker profile') }}</p>
                <p class="text-sm text-stone-500">{{ ! $caretaker ? __('Not created yet') : ($caretaker->is_active ? __('Live in the caretaker search') : __('Hidden by an admin')) }}</p>
                <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $caretaker ? __('Edit') : __('Create it') }} →</p>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <div class="lg:col-span-3 bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
                <h3 class="font-bold text-stone-900">{{ __('Recent sales') }}</h3>
                <a href="{{ route('seller.sales.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">{{ __('View all') }}</a>
            </div>
            @forelse ($recentSales as $sale)
                <div class="flex items-center gap-4 px-6 py-3.5 border-b border-stone-100 last:border-0">
                    <span class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">{{ mb_strtoupper(mb_substr($sale->order->buyer->name, 0, 1)) }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-stone-900 truncate">{{ $sale->animal_name }}</p>
                        <p class="text-xs text-stone-500">{{ $sale->order->buyer->name }} · #{{ $sale->order_id }} · {{ $sale->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="font-extrabold text-stone-900">{{ inr($sale->price * $sale->quantity, 0) }}</span>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <p class="text-4xl">📈</p>
                    <p class="mt-3 font-semibold text-stone-700">{{ __('No sales yet') }}</p>
                    <p class="text-sm text-stone-500">{{ __('Sales appear here as soon as a buyer orders your animals.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100"><h3 class="font-bold text-stone-900">{{ __('Needs attention') }}</h3></div>
                @forelse ($lowStock as $animal)
                    <a href="{{ route('seller.animals.edit', $animal) }}" class="flex items-center gap-3 px-6 py-3 hover:bg-stone-50 border-b border-stone-100 last:border-0">
                        <img src="{{ $animal->imageUrl() }}" class="w-10 h-10 rounded-lg object-cover bg-stone-100" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-stone-900 truncate text-sm">{{ $animal->name }}</p>
                            <p class="text-xs {{ $animal->stock === 0 ? 'text-red-600' : 'text-amber-700' }} font-semibold">{{ $animal->stock === 0 ? __('Sold out') : $animal->stock.' '.__('left') }}</p>
                        </div>
                        <span class="text-xs font-semibold text-amber-700">{{ __('Restock') }}</span>
                    </a>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-stone-500">✅ {{ __('All your listings are well stocked.') }}</p>
                @endforelse
            </div>

            <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
                <h3 class="font-bold text-stone-900">{{ __('Quick actions') }}</h3>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm font-semibold">
                    <a href="{{ route('seller.animals.create') }}" class="rounded-xl bg-amber-50 text-amber-800 px-3 py-3 text-center hover:bg-amber-100">+ {{ __('Add animal') }}</a>
                    <a href="{{ route('seller.animals.index') }}" class="rounded-xl bg-stone-100 text-stone-700 px-3 py-3 text-center hover:bg-stone-200">{{ __('My animals') }}</a>
                    <a href="{{ route('messages.index') }}" class="rounded-xl bg-stone-100 text-stone-700 px-3 py-3 text-center hover:bg-stone-200">{{ __('Chats') }}</a>
                    <a href="{{ route('home') }}" class="rounded-xl bg-stone-100 text-stone-700 px-3 py-3 text-center hover:bg-stone-200">{{ __('View site') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-panel-layout>
