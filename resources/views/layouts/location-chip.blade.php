{{-- "Your location" control shown in the top bar (popover) and in the phone menu (inline). --}}
@php $loc = \App\Support\Nearby::current(); @endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="{{ ($inline ?? false) ? '' : 'relative' }}">
    <button type="button" @click="open = ! open" class="{{ ($inline ?? false) ? 'w-full flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg bg-amber-50 text-amber-900 text-sm font-semibold' : 'flex items-center gap-1.5 max-w-[190px] pl-2.5 pr-2 py-1.5 rounded-full border border-stone-200 hover:border-amber-400 text-sm font-semibold text-stone-700' }}">
        <span class="flex items-center gap-1.5 min-w-0"><span>📍</span><span class="truncate">{{ isset($loc['city']) ? __($loc['city']) : __('Choose location') }}</span></span>
        <svg class="w-4 h-4 text-stone-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
    </button>

    <div x-show="open" @click.outside="open = false" style="display: none;"
         class="{{ ($inline ?? false) ? 'mt-2' : 'absolute right-0 top-full mt-2 w-[22rem] z-40' }} bg-white rounded-2xl border border-stone-200 shadow-2xl p-4 text-left"
         x-data="locationDetect({ url: @js(route('location.detect')), messages: { unsupported: @js(__('Location is not available on this device - please choose your city.')), denied: @js(__('Location permission is turned off - please choose your city.')), failed: @js(__('We could not get your location - please choose your city.')) } })">
        <p class="text-sm font-bold text-stone-900">{{ __('Your location') }}</p>
        <p class="text-xs text-stone-500 mt-0.5">{{ __('Animals and breeding within :a km, doctors and caretakers within :d km.', ['a' => \App\Support\Nearby::ANIMAL_RADIUS_KM, 'd' => \App\Support\Nearby::VET_RADIUS_KM]) }}</p>

        <form method="POST" action="{{ route('location.select') }}" class="mt-3">
            @csrf
            <x-location-picker :state="$loc['state'] ?? null" :city="$loc['city'] ?? null" :stacked="true" />
            <x-primary-button class="mt-3 w-full justify-center">{{ __('Save location') }}</x-primary-button>
        </form>

        <button type="button" @click="detect()" :disabled="busy" class="mt-2 w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-800 text-sm font-semibold hover:bg-sky-100 disabled:opacity-60">
            📍 <span x-text="busy ? @js(__('Finding you...')) : @js(__('Use my current location'))"></span>
        </button>
        <p x-show="error" x-text="error" style="display: none;" class="mt-2 text-xs text-amber-700 font-medium"></p>
    </div>
</div>
