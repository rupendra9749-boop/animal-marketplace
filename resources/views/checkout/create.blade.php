<x-app-layout>
    <x-slot name="header"><h2>{{ __('Checkout') }}</h2></x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
        <form method="POST" action="{{ route('checkout.store') }}" class="lg:col-span-3 bg-white rounded-2xl border border-stone-200/70 p-6 sm:p-8">
            @csrf
            <h3 class="font-extrabold text-stone-900 text-lg">{{ __('Delivery details') }}</h3>
            <p class="text-sm text-stone-500 mt-1">{{ __('Tell us where the animal should be delivered.') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <x-input-label for="shipping_name" :value="__('Full name')" />
                    <x-text-input id="shipping_name" name="shipping_name" class="block mt-1 w-full rounded-xl" value="{{ old('shipping_name', auth()->user()->name) }}" required />
                    <x-input-error :messages="$errors->get('shipping_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="shipping_address" :value="__('Delivery address')" />
                    <textarea id="shipping_address" name="shipping_address" rows="4" required class="block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500" placeholder="{{ __('House / village, street, city, PIN code') }}">{{ old('shipping_address') }}</textarea>
                    <x-input-error :messages="$errors->get('shipping_address')" class="mt-2" />
                </div>
            </div>

            <x-primary-button class="mt-8 w-full justify-center py-3.5 text-sm">{{ __('Place order') }} · {{ inr($total, 2) }}</x-primary-button>
        </form>

        <aside class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/70 p-6 lg:sticky lg:top-24">
            <h3 class="font-bold text-stone-900">{{ __('Order summary') }}</h3>
            <div class="mt-4 divide-y divide-stone-100">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3 py-3">
                        <img src="{{ $item['animal']->imageUrl() }}" class="w-14 h-14 rounded-xl object-cover bg-stone-100" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-stone-900 truncate">{{ $item['animal']->name }}</p>
                            <p class="text-xs text-stone-500">{{ __('Qty') }} {{ $item['quantity'] }}</p>
                        </div>
                        <p class="font-bold text-stone-900">{{ inr($item['subtotal'], 2) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-stone-100 flex justify-between text-lg font-extrabold text-stone-900">
                <span>{{ __('Total') }}</span>
                <span>{{ inr($total, 2) }}</span>
            </div>
        </aside>
    </div>
</x-app-layout>
