@props(['state' => null, 'city' => null, 'stateName' => 'state', 'cityName' => 'city', 'required' => true, 'stacked' => false])

@php
    $id = 'loc-'.\Illuminate\Support\Str::random(5);
    $field = 'block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500';
@endphp

<div {{ $attributes->merge(['class' => $stacked ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-1 sm:grid-cols-2 gap-3']) }}
     x-data="locationPicker({ state: @js((string) $state), city: @js((string) $city), url: @js(route('locations.cities')), hi: @js(app()->getLocale() === 'hi') })">
    <div>
        <label for="{{ $id }}-state" class="block font-medium text-sm text-gray-700">{{ __('State / Union Territory') }}</label>
        <select id="{{ $id }}-state" name="{{ $stateName }}" x-model="state" @change="changeState()" @if ($required) required @endif class="{{ $field }}">
            <option value="">{{ __('Select state') }}</option>
            @foreach (\App\Support\Locations::states() as $name)
                <option value="{{ $name }}" @selected($state === $name)>{{ __($name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="relative">
        <label for="{{ $id }}-city" class="block font-medium text-sm text-gray-700">{{ __('City / Town') }}</label>
        <input id="{{ $id }}-city" type="text" x-model="query" @input="typed()" @focus="open = true" @blur="closeSoon()" @keydown.enter.prevent="enter()" @keydown.escape="open = false"
               :disabled="! state" autocomplete="off" placeholder="{{ __('Type to search your city') }}" class="{{ $field }} disabled:bg-stone-100 disabled:text-stone-400">
        <input type="hidden" name="{{ $cityName }}" :value="city">
        <ul x-show="open && state" style="display: none;" class="absolute z-30 mt-1 w-full max-h-56 overflow-auto rounded-xl border border-stone-200 bg-white shadow-xl py-1 text-sm">
            <template x-for="c in matches" :key="c.n">
                <li><button type="button" @mousedown.prevent="pick(c)" class="block w-full text-left px-3 py-2 hover:bg-amber-50" x-text="label(c)"></button></li>
            </template>
            <li x-show="loading" class="px-3 py-2 text-stone-400">{{ __('Loading...') }}</li>
            <li x-show="! loading && matches.length === 0" class="px-3 py-2 text-stone-400">{{ __('No match - check the spelling') }}</li>
        </ul>
        <p class="mt-1 text-xs text-stone-400" x-show="state && ! city && query" style="display: none;">{{ __('Tap a city from the list.') }}</p>
    </div>
</div>
