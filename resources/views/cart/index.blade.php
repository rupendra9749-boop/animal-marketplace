<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Your Cart') }}</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <x-flash class="mb-6" />

        @if ($items->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-stone-300 p-12 text-center max-w-2xl mx-auto">
                <p class="text-5xl">🛒</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('Your cart is empty') }}</h3>
                <p class="mt-2 text-sm text-stone-500">{{ __('Find an animal you love and add it here.') }}</p>
                <a href="{{ route('home') }}" class="inline-block mt-6"><x-primary-button type="button">{{ __('Browse animals') }}</x-primary-button></a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-4">
                    @if ($items->count() >= 2)
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-amber-900">⚖️ {{ __('Can\'t decide?') }}</p>
                                <p class="text-sm text-amber-800">{{ __('Compare the animals in your cart side by side and keep the best one.') }}</p>
                            </div>
                            <form method="POST" action="{{ route('compare.from-cart') }}">
                                @csrf
                                <x-primary-button>{{ __('Compare cart items') }}</x-primary-button>
                            </form>
                        </div>
                    @endif

                    @if ($items->count() >= 2)
                        <a href="{{ route('breeding.index', ['a' => $items[0]['animal']->id, 'b' => $items[1]['animal']->id]) }}" class="flex items-center gap-3 rounded-2xl bg-rose-50 border border-rose-100 px-4 py-3 hover:bg-rose-100 transition">
                            <span class="text-xl">🧬</span>
                            <span class="flex-1 text-sm font-semibold text-rose-800">{{ __('Buying these two to breed? Check their breeding compatibility.') }}</span>
                            <span class="text-rose-700">→</span>
                        </a>
                    @endif

                    <div class="bg-white rounded-2xl border border-stone-200/70 divide-y divide-stone-100">
                        @foreach ($items as $item)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                                <img src="{{ $item['animal']->imageUrl() }}" class="w-20 h-20 object-cover rounded-xl bg-stone-100">

                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('animals.show', $item['animal']) }}" class="font-bold text-stone-900 hover:text-amber-700">{{ $item['animal']->name }}</a>
                                    <p class="text-xs text-stone-500 mt-0.5">{{ collect([$item['animal']->breed, $item['animal']->age, $item['animal']->location])->filter()->join(' · ') }}</p>
                                    <p class="text-sm text-stone-600 mt-1">{{ inr($item['animal']->price, 2) }} {{ __('each') }}</p>
                                </div>

                                <form method="POST" action="{{ route('cart.update', $item['animal']) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <x-text-input type="number" name="quantity" min="1" max="{{ $item['animal']->stock }}" value="{{ $item['quantity'] }}" class="w-20 text-sm" />
                                    <x-secondary-button type="submit">{{ __('Update') }}</x-secondary-button>
                                </form>

                                <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 sm:w-28">
                                    <p class="font-extrabold text-stone-900">{{ inr($item['subtotal'], 2) }}</p>
                                    <form method="POST" action="{{ route('cart.destroy', $item['animal']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">{{ __('Remove') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <aside class="bg-white rounded-2xl border border-stone-200/70 p-6 lg:sticky lg:top-24">
                    <h3 class="font-bold text-stone-900">{{ __('Order summary') }}</h3>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-stone-600">
                            <dt>{{ __('Items') }}</dt>
                            <dd>{{ $items->sum('quantity') }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-stone-100 pt-3 text-base font-extrabold text-stone-900">
                            <dt>{{ __('Total') }}</dt>
                            <dd>{{ inr($total, 2) }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('checkout.create') }}" class="block mt-6">
                        <x-primary-button type="button" class="w-full justify-center py-3">{{ __('Proceed to Checkout') }}</x-primary-button>
                    </a>
                    <a href="{{ route('home') }}" class="block mt-3 text-center text-sm text-stone-500 hover:text-stone-800">{{ __('Continue shopping') }}</a>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
