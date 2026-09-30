@props(['radius', 'what' => 'animals'])

<div id="location-gate" class="max-w-xl mx-auto bg-white rounded-3xl border border-stone-200/70 shadow-xl shadow-stone-200/50 p-6 sm:p-8 text-center"
     x-data="locationDetect({ url: @js(route('location.detect')), messages: { unsupported: @js(__('Location is not available on this device - please choose your city.')), denied: @js(__('Location permission is turned off - please choose your city.')), failed: @js(__('We could not get your location - please choose your city.')) } })">
    <span class="mx-auto w-14 h-14 rounded-2xl bg-amber-50 text-3xl flex items-center justify-center">📍</span>
    <h2 class="mt-4 text-xl font-extrabold text-stone-900">{{ __('Where are you?') }}</h2>
    <p class="mt-1 text-sm text-stone-500">{{ __('To keep things useful, we show :what within :km km of you. Choose your city to start.', ['what' => __($what), 'km' => $radius]) }}</p>

    <form method="POST" action="{{ route('location.select') }}" class="mt-5 text-left">
        @csrf
        <x-location-picker />
        <x-primary-button class="mt-4 w-full justify-center py-3">{{ __('Show :what near me', ['what' => __($what)]) }}</x-primary-button>
    </form>

    <div class="mt-4 flex items-center gap-3 text-xs text-stone-400"><span class="flex-1 h-px bg-stone-200"></span>{{ __('or') }}<span class="flex-1 h-px bg-stone-200"></span></div>

    <button type="button" @click="detect()" :disabled="busy" class="mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-3 rounded-lg border border-sky-200 bg-sky-50 text-sky-800 text-sm font-semibold hover:bg-sky-100 disabled:opacity-60">
        📍 <span x-text="busy ? @js(__('Finding you...')) : @js(__('Use my current location'))"></span>
    </button>
    <p x-show="error" x-text="error" style="display: none;" class="mt-3 text-sm text-amber-700 font-medium"></p>
</div>
