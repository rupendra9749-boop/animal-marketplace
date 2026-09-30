{{-- Shared by the public "list your clinic" page and the admin add/edit pages. --}}
@csrf
@php
    $field = 'block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-sky-500 focus:ring-sky-500';
    $section = 'bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6';
    $selectedTypes = collect(old('categories', isset($vet) && $vet->exists ? $vet->categories->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $admin = $admin ?? false;
    $withPhoto = $admin || ($withPhoto ?? false);
@endphp

<div class="space-y-5">
    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('About the doctor') }}</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Doctor name')" />
                <x-text-input id="name" name="name" class="{{ $field }}" value="{{ old('name', $vet->name ?? '') }}" required placeholder="{{ __('Dr. Anil Sharma') }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="clinic_name" :value="__('Clinic / hospital name')" />
                <x-text-input id="clinic_name" name="clinic_name" class="{{ $field }}" value="{{ old('clinic_name', $vet->clinic_name ?? '') }}" />
                <x-input-error :messages="$errors->get('clinic_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="qualification" :value="__('Qualification')" />
                <x-text-input id="qualification" name="qualification" class="{{ $field }}" value="{{ old('qualification', $vet->qualification ?? '') }}" placeholder="{{ __('BVSc & AH, MVSc') }}" />
                <x-input-error :messages="$errors->get('qualification')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="experience_years" :value="__('Years of experience')" />
                <x-text-input id="experience_years" name="experience_years" type="number" min="0" max="70" class="{{ $field }}" value="{{ old('experience_years', $vet->experience_years ?? '') }}" />
                <x-input-error :messages="$errors->get('experience_years')" class="mt-2" />
            </div>
        </div>
        <div class="mt-4">
            <x-input-label for="about" :value="__('About you')" />
            <textarea id="about" name="about" rows="3" class="{{ $field }}" placeholder="{{ __('A few lines about your practice.') }}">{{ old('about', $vet->about ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('about')" class="mt-2" />
        </div>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Animals treated') }}</h3>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($categories as $category)
                <label class="cursor-pointer">
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="peer sr-only" @checked($selectedTypes->contains($category->id))>
                    <span class="inline-flex items-center px-4 py-2 rounded-full border-2 border-stone-200 text-sm font-semibold text-stone-600 peer-checked:border-sky-500 peer-checked:bg-sky-50 peer-checked:text-sky-800 peer-focus-visible:ring-2 peer-focus-visible:ring-sky-400">{{ __($category->name) }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('categories')" class="mt-2" />
        <div class="mt-4">
            <x-input-label for="services" :value="__('Services (separate with commas)')" />
            <x-text-input id="services" name="services" class="{{ $field }}" value="{{ old('services', $vet->services ?? '') }}" placeholder="{{ __('Vaccination, Surgery, Artificial insemination, Deworming') }}" />
            <x-input-error :messages="$errors->get('services')" class="mt-2" />
        </div>
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-3">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                <input type="checkbox" name="home_visit" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('home_visit', $vet->home_visit ?? false))> 🏠 {{ __('Visits animals at home / farm') }}
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700 cursor-pointer">
                <input type="checkbox" name="emergency" value="1" class="rounded border-stone-300 text-red-600 focus:ring-red-500" @checked(old('emergency', $vet->emergency ?? false))> 🚨 {{ __('Available 24x7 for emergencies') }}
            </label>
        </div>
    </div>

    <div class="{{ $section }}">
        <h3 class="font-bold text-stone-900">{{ __('Location & contact') }}</h3>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-location-picker :state="old('state', $vet->state ?? null)" :city="old('city', $vet->city ?? null)" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="address" :value="__('Address')" />
                <x-text-input id="address" name="address" class="{{ $field }}" value="{{ old('address', $vet->address ?? '') }}" placeholder="{{ __('Street, area') }}" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="tel" class="{{ $field }}" value="{{ old('phone', $vet->phone ?? '') }}" required placeholder="98765 43210 or 0161 2345678" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="whatsapp" :value="__('WhatsApp (optional)')" />
                <x-text-input id="whatsapp" name="whatsapp" type="tel" class="{{ $field }}" value="{{ old('whatsapp', $vet->whatsapp ?? '') }}" />
                <x-input-error :messages="$errors->get('whatsapp')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email (optional)')" />
                <x-text-input id="email" name="email" type="email" class="{{ $field }}" value="{{ old('email', $vet->email ?? '') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="timings" :value="__('Timings')" />
                <x-text-input id="timings" name="timings" class="{{ $field }}" value="{{ old('timings', $vet->timings ?? '') }}" placeholder="{{ __('Mon-Sat, 9 am - 7 pm') }}" />
                <x-input-error :messages="$errors->get('timings')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="consultation_fee" :value="__('Consultation fee (₹, optional)')" />
                <x-text-input id="consultation_fee" name="consultation_fee" type="number" step="0.01" min="0" class="{{ $field }}" value="{{ old('consultation_fee', $vet->consultation_fee ?? '') }}" />
                <x-input-error :messages="$errors->get('consultation_fee')" class="mt-2" />
            </div>
        </div>
    </div>

    @if ($withPhoto)
        <div class="{{ $section }}" x-data="{ preview: null }">
            <h3 class="font-bold text-stone-900">{{ $admin ? __('Photo & visibility') : __('Photo') }}</h3>
            <div class="mt-4 flex items-center gap-4">
                <div class="w-24 h-24 rounded-2xl bg-stone-100 border border-dashed border-stone-300 overflow-hidden flex items-center justify-center shrink-0">
                    <img x-show="preview" :src="preview" class="w-full h-full object-cover" style="display:none" alt="">
                    @if ($vet->exists && $vet->photoUrl())
                        <img x-show="!preview" src="{{ $vet->photoUrl() }}" class="w-full h-full object-cover" alt="">
                    @else
                        <span x-show="!preview" class="text-3xl">🩺</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <input id="photo" name="photo" type="file" accept="image/*" @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null"
                           class="block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-50 file:px-4 file:py-2 file:font-semibold file:text-sky-800 hover:file:bg-sky-100">
                    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                </div>
            </div>
            @if ($admin)
                <label class="mt-5 flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $vet->is_active ?? true))>
                    <span class="text-sm font-semibold text-stone-700">{{ __('Visible to buyers') }}</span>
                </label>
            @endif
        </div>
    @endif
</div>
