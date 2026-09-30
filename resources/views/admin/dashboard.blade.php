<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="rounded-2xl bg-gradient-to-r from-stone-900 to-stone-800 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-stone-400 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('Welcome back,') }} {{ auth()->user()->name }} 👋</h1>
            <p class="text-stone-400 text-sm mt-1">{{ __('Here\'s what\'s happening on your marketplace today.') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.sellers.create') }}" class="bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold px-4 py-2.5 rounded-xl">+ {{ __('Add Seller') }}</a>
            <a href="{{ route('admin.animals.index') }}" class="bg-white/10 hover:bg-white/20 text-white text-sm font-bold px-4 py-2.5 rounded-xl">{{ __('Review animals') }}</a>
        </div>
    </div>

    @php $pendingVets = \App\Models\Vet::where('is_active', false)->count(); @endphp
    @if ($pendingVets)
        <a href="{{ route('admin.vets.index', ['status' => 'pending']) }}" class="flex items-center gap-3 rounded-2xl bg-sky-50 border border-sky-100 px-5 py-3.5 hover:bg-sky-100 transition">
            <span class="text-2xl">🩺</span>
            <span class="flex-1 text-sm font-semibold text-sky-900">{{ $pendingVets }} {{ $pendingVets === 1 ? __('doctor is') : __('doctors are') }} {{ __('waiting for your approval') }}</span>
            <span class="text-sky-700 font-bold">{{ __('Review') }} →</span>
        </a>
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ([
            ['label' => __('Total Revenue'), 'value' => inr($stats['revenue'], 0), 'sub' => $stats['orders'].' '.($stats['orders'] === 1 ? __('order') : __('orders')), 'color' => 'bg-emerald-500', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
            ['label' => __('Pending Orders'), 'value' => $stats['pending_orders'], 'sub' => __('need attention'), 'color' => 'bg-amber-500', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'href' => route('admin.orders.index')],
            ['label' => __('Animals Listed'), 'value' => $stats['animals'], 'sub' => $stats['active_animals'].' '.__('active'), 'color' => 'bg-sky-500', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'href' => route('admin.animals.index')],
            ['label' => __('Users'), 'value' => $stats['sellers'] + $stats['buyers'], 'sub' => $stats['sellers'].' '.__('sellers').' · '.$stats['buyers'].' '.__('buyers'), 'color' => 'bg-violet-500', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'href' => route('admin.users.index')],
        ] as $card)
            <a href="{{ $card['href'] ?? '#' }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-500">{{ $card['label'] }}</p>
                    <span class="w-10 h-10 rounded-xl {{ $card['color'] }} text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $card['value'] }}</p>
                <p class="text-xs text-stone-500 mt-1">{{ $card['sub'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
                <h3 class="font-bold text-stone-900">{{ __('Recent Orders') }}</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">{{ __('View all') }}</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-stone-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">{{ __('Order') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Buyer') }}</th>
                        <th class="px-6 py-3 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($recentOrders as $order)
                        <tr class="hover:bg-stone-50">
                            <td class="px-6 py-3"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-stone-900 hover:text-amber-700">#{{ $order->id }}</a></td>
                            <td class="px-6 py-3">{{ $order->buyer->name }}</td>
                            <td class="px-6 py-3"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-6 py-3 text-right font-semibold">{{ inr($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-stone-400">{{ __('No orders yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
                <h3 class="font-bold text-stone-900">{{ __('Contact Inbox') }}</h3>
                @if ($stats['unread_contacts'])
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">{{ $stats['unread_contacts'] }} {{ __('new') }}</span>
                @endif
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($recentContacts as $contact)
                    <a href="{{ route('admin.contact-messages.show', $contact) }}" class="block px-6 py-3 hover:bg-stone-50">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm {{ $contact->read_at ? 'font-medium text-stone-700' : 'font-bold text-stone-900' }} truncate">{{ $contact->name }}</p>
                            <span class="text-[11px] text-stone-400 shrink-0">{{ $contact->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-stone-500 truncate">{{ $contact->subject }}</p>
                    </a>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-stone-400">{{ __('No messages yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
            <h3 class="font-bold text-stone-900">{{ __('Latest Listings') }}</h3>
            <a href="{{ route('admin.animals.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="divide-y divide-stone-100">
            @foreach ($latestAnimals as $animal)
                <a href="{{ route('admin.animals.show', $animal) }}" class="flex items-center gap-4 px-6 py-3 hover:bg-stone-50">
                    <img src="{{ $animal->imageUrl() }}" class="w-11 h-11 rounded-xl object-cover bg-stone-100">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-stone-900 truncate">{{ $animal->name }}</p>
                        <p class="text-xs text-stone-500">{{ __($animal->category?->name) }} · {{ __('by') }} {{ $animal->seller->name }}</p>
                    </div>
                    <span class="text-sm font-bold text-stone-900">{{ inr($animal->price, 0) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $animal->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">{{ $animal->is_active ? __('Active') : __('Hidden') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</x-admin-layout>
