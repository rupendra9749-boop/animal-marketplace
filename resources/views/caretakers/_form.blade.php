{{-- Shared by the public "offer your care services" page, the caretaker panel and the admin add/edit pages. --}}
@csrf
@php
    $field = 'block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-emerald-500 focus:ring-emerald-500';
    $section = 'bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6';
    $selectedTypes = collect(old('categories', isset($caretaker) && $caretaker->exists ? $caretaker->categories->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $admin = $admin ?? false;
    $withPhoto = $admin || ($withPhoto ?? false);
@endphp

<div class="space-y-5">
    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('About you') }}</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Your name')" />
                <x-text-input id="name" name="name" class="{{ $field }}" value="{{ old('name', $caretaker->name ?? '') }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="experience_years" :value="__('Years of experience')" />
                <x-text-input id="experience_years" name="experience_years" type="number" min="0" max="70" class="{{ $field }}" value="{{ old('experience_years', $caretaker->experience_years ?? '') }}" />
                <x-input-error :messages="$errors->get('experience_years')" class="mt-2" />
            </div>
        </div>
        <div class="mt-4">
            <x-input-label for="headline" :value="__('Headline')" />
            <x-text-input id="headline" name="headline" class="{{ $field }}" value="{{ old('headline', $caretaker->headline ?? '') }}" placeholder="{{ __('e.g. Dairy farm caretaker with 8 years experience') }}" />
            <x-input-error :messages="$errors->get('headline')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="about" :value="__('About you')" />
            <textarea id="about" name="about" rows="3" class="{{ $field }}" placeholder="{{ __('What you do, what animals you are comfortable with, your routine.') }}">{{ old('about', $caretaker->about ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('about')" class="mt-2" />
        </div>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Animals you look after') }}</h3>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($categories as $category)
                <label class="cursor-pointer">
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="peer sr-only" @checked($selectedTypes->contains($category->id))>
                    <span class="inline-flex items-center px-4 py-2 rounded-full border-2 border-stone-200 text-sm font-semibold text-stone-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400">{{ __($category->name) }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('categories')" class="mt-2" />

        <div class="mt-4">
            <x-input-label for="services" :value="__('Services (separate with commas)')" />
            <x-text-input id="services" name="services" class="{{ $field }}" value="{{ old('services', $caretaker->services ?? '') }}" placeholder="{{ __('Daily feeding, Milking, Grooming, Night watch, Walking') }}" />
            <x-input-error :messages="$errors->get('services')" class="mt-2" />
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="rate" :value="__('Your rate (₹)')" />
                <x-text-input id="rate" name="rate" type="number" step="1" min="0" class="{{ $field }}" value="{{ old('rate', isset($caretaker->rate) ? (int) $caretaker->rate : '') }}" />
                <x-input-error :messages="$errors->get('rate')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="rate_unit" :value="__('Charged')" />
                <select id="rate_unit" name="rate_unit" class="{{ $field }}">
                    @foreach (\App\Models\Caretaker::RATE_UNITS as $value => $label)
                        <option value="{{ $value }}" @selected(old('rate_unit', $caretaker->rate_unit ?? 'day') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-3">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                <input type="checkbox" name="home_visit" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('home_visit', $caretaker->home_visit ?? false))> 🏠 {{ __('I come to the animal (home / farm)') }}
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                <input type="checkbox" name="boarding" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('boarding', $caretaker->boarding ?? false))> 🛏️ {{ __('I keep animals at my place (boarding)') }}
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                <input type="checkbox" name="available" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('available', $caretaker->available ?? true))> ✅ {{ __('I am available for new work') }}
            </label>
        </div>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Location & contact') }}</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-location-picker :state="old('state', $caretaker->state ?? null)" :city="old('city', $caretaker->city ?? null)" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                <p class="mt-1 text-xs text-stone-400">{{ __('People within :km km of this city will find you.', ['km' => \App\Support\Nearby::CARETAKER_RADIUS_KM]) }}</p>
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="address" :value="__('Area / address')" />
                <x-text-input id="address" name="address" class="{{ $field }}" value="{{ old('address', $caretaker->address ?? '') }}" placeholder="{{ __('Village, area or street') }}" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="tel" class="{{ $field }}" value="{{ old('phone', $caretaker->phone ?? '') }}" required placeholder="98765 43210" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="whatsapp" :value="__('WhatsApp (optional)')" />
                <x-text-input id="whatsapp" name="whatsapp" type="tel" class="{{ $field }}" value="{{ old('whatsapp', $caretaker->whatsapp ?? '') }}" />
                <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <x-input-label for="email" :value="__('Email (optional)')" />
                <x-text-input id="email" name="email" type="email" class="{{ $field }}" value="{{ old('email', $caretaker->email ?? '') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>
    </div>

    @if ($withPhoto)
        <div class="{{ $section }}" x-data="{ preview: null }">
            <h3 class="font-bold text-stone-900">{{ $admin ? __('Photo & visibility') : __('Photo') }}</h3>
            <div class="mt-4 flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl bg-stone-100 border border-dashed border-stone-300 overflow-hidden flex items-center justify-center shrink-0">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover" style="display:none" alt="">
                    @if ($caretaker->exists && $caretaker->photoUrl())
                        <img x-show="!preview" src="{{ $caretaker->photoUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <span x-show="!preview" class="text-3xl">🤝</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <input id="photo" name="photo" type="file" accept="image/*" @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null"
                           class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:font-semibold file:text-emerald-800 hover:file:bg-emerald-100">
                    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                </div>
            </div>
            @if ($admin)
                <label class="mt-5 flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $caretaker->is_active ?? true))>
                    <span class="text-sm font-semibold text-stone-700">{{ __('Visible to buyers') }}</span>
                </label>
            @endif
        </div>
    @endif
</div>
