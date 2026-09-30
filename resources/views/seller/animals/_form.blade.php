@php $area = $area ?? 'seller'; @endphp
@csrf
@php
    $breederMode = $area === 'breeder';
    $field = 'block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500';
    $section = 'bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6';
@endphp

<div class="space-y-5" x-data="{ type: '{{ $breederMode ? 'breeding' : old('listing_type', $animal->listing_type ?? 'sale') }}' }">
    @if ($breederMode)
        <input type="hidden" name="listing_type" value="breeding">
        <div class="rounded-2xl bg-rose-50 border border-rose-100 px-5 py-4 text-sm text-rose-900">🧬 {{ __('This animal will be listed for breeding. Buyers near you can find it, check the match and message you.') }}</div>
    @else
    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('What is this listing for?') }}</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ([
                'sale' => ['🛒', __('For sale'), __('Buyers can add it to their cart and buy it.')],
                'breeding' => ['🧬', __('Breeding only'), __('Not for sale - you offer it for breeding and charge a fee.')],
                'both' => ['🐾', __('Sale + breeding'), __('It can be bought, and is also shown in Breeding animals.')],
            ] as $value => [$icon, $label, $hint])
                <label class="cursor-pointer rounded-xl border-2 p-4 transition"
                       :class="type === '{{ $value }}' ? 'border-amber-500 bg-amber-50' : 'border-stone-200 hover:border-stone-300'">
                    <input type="radio" name="listing_type" value="{{ $value }}" x-model="type" class="sr-only">
                    <span class="text-2xl">{{ $icon }}</span>
                    <span class="block mt-1 font-bold text-stone-900">{{ $label }}</span>
                    <span class="block mt-0.5 text-xs text-stone-500 leading-snug">{{ $hint }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('listing_type')" class="mt-2" />
    </div>
    @endif

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Basic information') }}</h3>
        <div class="mt-4 space-y-4">
            <div>
                <x-input-label for="name" :value="__('Listing title')" />
                <x-text-input id="name" name="name" class="{{ $field }}" value="{{ old('name', $animal->name ?? '') }}" required placeholder="{{ __('e.g. Holstein Dairy Cow') }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="category_id" :value="__('Animal type')" />
                    <select id="category_id" name="category_id" class="{{ $field }}">
                        <option value="">{{ __('Choose type') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $animal->category_id ?? '') == $category->id)>{{ __($category->name) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="breed" :value="__('Breed')" />
                    <x-text-input id="breed" name="breed" class="{{ $field }}" value="{{ old('breed', $animal->breed ?? '') }}" placeholder="{{ __('e.g. Sahiwal') }}" />
                    <x-input-error :messages="$errors->get('breed')" class="mt-2" />
                </div>
            </div>
            <div>
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="4" class="{{ $field }}" placeholder="{{ __('Health, temperament, milk yield, training, reason for selling...') }}">{{ old('description', $animal->description ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Animal details') }}</h3>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
                <x-input-label for="age" :value="__('Age')" />
                <x-text-input id="age" name="age" class="{{ $field }}" value="{{ old('age', $animal->age ?? '') }}" placeholder="{{ __('e.g. 2 years') }}" />
                <x-input-error :messages="$errors->get('age')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="gender" :value="__('Gender')" />
                <select id="gender" name="gender" class="{{ $field }}">
                    @foreach (['male' => 'Male', 'female' => 'Female', 'unknown' => 'Unknown'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $animal->gender ?? 'unknown') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="color" :value="__('Color')" />
                <x-text-input id="color" name="color" class="{{ $field }}" value="{{ old('color', $animal->color ?? '') }}" placeholder="{{ __('e.g. Black & White') }}" />
                <x-input-error :messages="$errors->get('color')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="weight" :value="__('Weight')" />
                <x-text-input id="weight" name="weight" class="{{ $field }}" value="{{ old('weight', $animal->weight ?? '') }}" placeholder="e.g. 450 kg" />
                <x-input-error :messages="$errors->get('weight')" class="mt-2" />
            </div>
            <div class="col-span-2 sm:col-span-3">
                <x-location-picker :state="old('state', $animal->state ?? null)" :city="old('city', $animal->location ?? null)" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                <p class="mt-1 text-xs text-stone-400">{{ __('Buyers within :km km of this city will see your animal.', ['km' => \App\Support\Nearby::ANIMAL_RADIUS_KM]) }}</p>
            </div>
        </div>
        <label class="mt-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 cursor-pointer">
            <input id="is_vaccinated" name="is_vaccinated" type="checkbox" value="1" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_vaccinated', $animal->is_vaccinated ?? false))>
            <span class="text-sm font-semibold text-emerald-800">{{ __('This animal is vaccinated') }}</span>
        </label>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Price & availability') }}</h3>
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div x-show="type !== 'breeding'">
                <x-input-label for="price" :value="__('Price (₹)')" />
                <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="{{ $field }}" value="{{ old('price', $animal->price ?? '') }}" x-bind:required="type !== 'breeding'" />
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>
            <div x-show="type !== 'sale'" style="display: none;">
                <x-input-label for="breeding_fee" :value="__('Breeding fee (₹ per service)')" />
                <x-text-input id="breeding_fee" name="breeding_fee" type="number" step="0.01" min="0" class="{{ $field }}" value="{{ old('breeding_fee', $animal->breeding_fee ?? '') }}" x-bind:required="type === 'breeding'" />
                <p class="mt-1 text-xs text-stone-500" x-show="type === 'both'">{{ __('Optional - leave empty to show "Ask owner".') }}</p>
                <x-input-error :messages="$errors->get('breeding_fee')" class="mt-2" />
            </div>
            <div x-show="type !== 'breeding'">
                <x-input-label for="stock" :value="__('Quantity available')" />
                <x-text-input id="stock" name="stock" type="number" min="0" class="{{ $field }}" value="{{ old('stock', $animal->stock ?? 1) }}" x-bind:required="type !== 'breeding'" />
                <x-input-error :messages="$errors->get('stock')" class="mt-2" />
            </div>
        </div>
        <label class="mt-5 flex items-center gap-3 cursor-pointer">
            <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" @checked(old('is_active', $animal->is_active ?? true))>
            <span class="text-sm font-semibold text-stone-700">{{ __('Visible to buyers') }}</span>
        </label>
    </div>

    <div class="{{ $section }}" x-data="{ preview: null }">
        <h3 class="font-bold text-stone-900">{{ __('Photo') }}</h3>
        <p class="text-xs text-stone-500 mt-1">{{ __('A clear photo helps your animal sell faster. Max 2 MB.') }}</p>
        <div class="mt-4 flex items-center gap-4">
            <div class="w-28 h-28 rounded-2xl bg-stone-100 border border-dashed border-stone-300 overflow-hidden flex items-center justify-center shrink-0">
                <img x-show="preview" :src="preview" class="w-full h-full object-cover" style="display:none" alt="">
                @if (isset($animal))
                    <img x-show="!preview" src="{{ $animal->imageUrl() }}" class="w-full h-full object-cover" alt="">
                @else
                    <span x-show="!preview" class="text-3xl">📷</span>
                @endif
            </div>
            <div class="min-w-0">
                <input id="image" name="image" type="file" accept="image/*"
                       @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null"
                       class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:font-semibold file:text-amber-800 hover:file:bg-amber-100">
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>
        </div>
    </div>
</div>
