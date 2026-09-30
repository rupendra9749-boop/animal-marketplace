<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Add Seller') }}</h2>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl items-start">
        <div class="lg:col-span-2 space-y-4">
            <x-admin.intro :title="__('Add a seller account')" :subtitle="__('Create a login for someone who will list animals. Share the email and password with them - they can change it later.')" />

            <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
                <form method="POST" action="{{ route('admin.sellers.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" value="{{ old('name') }}" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" value="{{ old('email') }}" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="phone" :value="__('Mobile number')" />
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-stone-50 text-sm font-semibold text-stone-500">+91</span>
                            <input id="phone" name="phone" type="tel" inputmode="numeric" maxlength="14" required placeholder="98765 43210" value="{{ preg_replace('/^\+91\s*/', '', old('phone', '')) }}"
                                   class="block w-full min-w-0 border-gray-300 rounded-r-md rounded-l-none text-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-location-picker :state="old('state')" :city="old('city')" />
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('admin.users.index') }}">
                            <x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button>
                        </a>
                        <x-primary-button>{{ __('Create seller') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="rounded-2xl bg-amber-50 border border-amber-100 p-5">
            <p class="font-bold text-amber-900">🐄 {{ __('What a seller can do') }}</p>
            <ul class="mt-3 space-y-2 text-sm text-amber-900/80 list-disc pl-5">
                <li>{{ __('Add, edit and remove their own animals') }}</li>
                <li>{{ __('List animals for sale, for breeding, or both') }}</li>
                <li>{{ __('Chat with buyers and see their sales') }}</li>
                <li>{{ __('Also shop like any buyer with the same login') }}</li>
            </ul>
        </aside>
    </div>
</x-admin-layout>
