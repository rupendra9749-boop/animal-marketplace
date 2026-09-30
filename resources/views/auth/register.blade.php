<x-guest-layout>
    <h1 class="text-2xl font-extrabold text-stone-900">{{ __('Create your account') }}</h1>
    <p class="text-sm text-stone-500 mt-1 mb-6">{{ __('Free forever. Everyone can browse and buy - tell us where you are so we can show what is near you.') }}</p>

    @php
        $field = 'block mt-1 w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500';

    @endphp

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" class="{{ $field }}" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="{{ $field }}" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Mobile number')" />
            <div class="mt-1 flex rounded-xl shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-stone-300 bg-stone-50 text-sm font-semibold text-stone-500">+91</span>
                <input id="phone" name="phone" type="tel" inputmode="numeric" value="{{ old('phone') ? preg_replace('/^\+91\s*/', '', old('phone')) : '' }}" required autocomplete="tel-national" maxlength="14" placeholder="98765 43210"
                       class="block w-full min-w-0 border-stone-300 rounded-r-xl rounded-l-none text-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div>
            <x-location-picker :state="old('state')" :city="old('city')" />
            <x-input-error :messages="$errors->get('state')" class="mt-2" />
            <x-input-error :messages="$errors->get('city')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="country" :value="__('Country')" />
            <input id="country" type="text" value="{{ \App\Support\Locations::COUNTRY }}" disabled class="{{ $field }} bg-stone-100 text-stone-500">
            <p class="mt-1 text-xs text-stone-400">{{ __('AnimalMandi works across all of India.') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="{{ $field }}" type="password" name="password" required autocomplete="new-password" />
            </div>
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="{{ $field }}" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>
        </div>
        <x-input-error :messages="$errors->get('password')" />

        <fieldset>
            <legend class="text-sm font-medium text-gray-700">{{ __('I want to') }}</legend>
            <div class="mt-2 grid grid-cols-1 gap-2">
                @foreach ([
                    'buyer' => ['🛒', __('Buy animals and use services'), __('Browse animals, breeding, doctors and caretakers near me')],
                    'seller' => ['🐄', __('Sell and offer services'), __('Sell animals, list breeding animals, be a doctor or a caretaker - and still buy')],
                ] as $value => [$icon, $label, $hint])
                    <label class="flex items-start gap-3 rounded-xl border border-stone-200 px-3 py-3 cursor-pointer has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="account_type" value="{{ $value }}" class="mt-1 border-stone-300 text-amber-600 focus:ring-amber-500" @checked(old('account_type', request('as') === 'seller' ? 'seller' : 'buyer') === $value)>
                        <span class="text-xl">{{ $icon }}</span>
                        <span class="min-w-0"><span class="block text-sm font-bold text-stone-900">{{ $label }}</span><span class="block text-xs text-stone-500">{{ $hint }}</span></span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </fieldset>

        <x-primary-button class="w-full justify-center py-3">{{ __('Create account') }}</x-primary-button>

        <p class="text-center text-sm text-stone-500">
            {{ __('Already registered?') }}
            <a class="font-semibold text-amber-700 hover:underline" href="{{ route('login') }}">{{ __('Log in') }}</a>
        </p>
    </form>
</x-guest-layout>
