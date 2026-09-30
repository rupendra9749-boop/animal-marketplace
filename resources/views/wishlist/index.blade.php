<x-panel-layout>
    <x-slot name="header"><h2>{{ __('My Wishlist') }}</h2></x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse ($animals as $animal)
            <x-animal-card :animal="$animal" :wishlisted="true" :compared="in_array($animal->id, session('compare', []))" />
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-12 text-center">
                <p class="text-5xl">💛</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('Your wishlist is empty') }}</h3>
                <p class="mt-2 text-sm text-stone-500">{{ __('Tap the heart on any listing to save it for later.') }}</p>
                <a href="{{ route('home') }}" class="inline-block mt-6"><x-primary-button type="button">{{ __('Browse animals') }}</x-primary-button></a>
            </div>
        @endforelse
    </div>

    {{ $animals->links() }}
</x-panel-layout>
