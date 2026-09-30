<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ $animal->name }}
            </h2>
            <a href="{{ route('admin.animals.index') }}" class="text-sm text-amber-700 hover:underline">{{ __('← Back to Animals') }}</a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded-md">{{ session('status') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <img src="{{ $animal->imageUrl() }}" class="w-full h-64 object-cover rounded-xl bg-stone-50 border border-stone-100">
        </div>

        <div class="md:col-span-2 bg-white shadow-sm rounded-xl border border-stone-100 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-amber-700 font-medium uppercase tracking-wide">{{ __($animal->category?->name ?? 'Uncategorized') }}</p>
                    <h1 class="text-2xl font-bold text-stone-900">{{ $animal->name }}</h1>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $animal->is_active ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-600' }}">
                    {{ $animal->is_active ? __('Active') : __('Hidden') }}
                </span>
            </div>

            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 text-sm">
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Breed') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ $animal->breed ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Age') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ $animal->age ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Gender') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ __(ucfirst($animal->gender)) }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Color') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ $animal->color ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Weight') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ $animal->weight ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Vaccinated') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ $animal->is_vaccinated ? __('Yes') : __('No') }}</dd>
                </div>
                <div>
                    <dt class="text-stone-400 uppercase text-xs">{{ __('Location') }}</dt>
                    <dd class="text-stone-800 font-medium">{{ __($animal->location) ?? '—' }}</dd>
                </div>
                @if ($animal->isForSale())
                    <div>
                        <dt class="text-stone-400 uppercase text-xs">{{ __('Price') }}</dt>
                        <dd class="text-amber-700 font-bold">{{ inr($animal->price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-400 uppercase text-xs">{{ __('Stock') }}</dt>
                        <dd class="text-stone-800 font-medium">{{ $animal->stock }}</dd>
                    </div>
                @endif
                @if ($animal->offersBreeding())
                    <div>
                        <dt class="text-stone-400 uppercase text-xs">🧬 {{ $animal->isBreedingOnly() ? __('Breeding only - fee') : __('Breeding fee') }}</dt>
                        <dd class="text-rose-700 font-bold">{{ (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee, 2) : __('Ask owner') }}</dd>
                    </div>
                @endif
            </dl>

            <div class="mt-6">
                <dt class="text-stone-400 uppercase text-xs">{{ __('Description') }}</dt>
                <dd class="text-stone-700 mt-1 whitespace-pre-line">{{ $animal->description ?: __('No description provided.') }}</dd>
            </div>

            <div class="mt-6 pt-6 border-t border-stone-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-stone-400 uppercase">{{ __('Seller') }}</p>
                    <p class="text-stone-800 font-medium">{{ $animal->seller->name }} &middot; {{ $animal->seller->email }}</p>
                </div>
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('admin.animals.update', $animal) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="{{ $animal->is_active ? 0 : 1 }}">
                        <x-secondary-button type="submit">{{ $animal->is_active ? __('Hide Listing') : __('Activate Listing') }}</x-secondary-button>
                    </form>
                    <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" onsubmit="return confirm('{{ __('Delete this animal?') }}')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-xl border border-stone-100 overflow-hidden">
        <h3 class="font-semibold text-stone-800 px-6 pt-6 pb-2">{{ __('Sales History') }}</h3>
        <table class="w-full text-sm text-left">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">{{ __('Order') }}</th>
                    <th class="px-6 py-3">{{ __('Buyer') }}</th>
                    <th class="px-6 py-3">{{ __('Quantity') }}</th>
                    <th class="px-6 py-3">{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($animal->orderItems as $item)
                    <tr>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-amber-700 hover:underline">#{{ $item->order_id }}</a>
                        </td>
                        <td class="px-6 py-3">{{ $item->order->buyer->name }}</td>
                        <td class="px-6 py-3">{{ $item->quantity }}</td>
                        <td class="px-6 py-3">{{ inr($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-stone-500">{{ __('No sales yet for this animal.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
