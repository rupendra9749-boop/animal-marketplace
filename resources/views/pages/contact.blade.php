<x-app-layout>
    <section class="bg-stone-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <span class="text-amber-400 text-sm font-bold uppercase tracking-wider">{{ __('Contact') }}</span>
            <h1 class="mt-3 text-4xl font-extrabold text-white tracking-tight">{{ __('Get in touch') }}</h1>
            <p class="mt-4 text-stone-300">{{ __('Have a question about buying, selling or your account? Send us a message and our team will reply within one business day.') }}</p>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="space-y-4">
            @foreach ([
                ['📧', __('Email'), 'support@animalmandi.test'],
                ['📞', __('Phone'), '+91 98765 43210'],
                ['🕘', __('Hours'), __('Mon–Sat, 9:00 AM – 6:00 PM')],
                ['📍', __('Office'), __('Sector 18, Noida, Uttar Pradesh')],
            ] as [$icon, $label, $value])
                <div class="bg-white rounded-2xl border border-stone-200/70 p-5 flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-xl flex items-center justify-center shrink-0">{{ $icon }}</div>
                    <div>
                        <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ $label }}</p>
                        <p class="font-semibold text-stone-800">{{ $value }}</p>
                    </div>
                </div>
            @endforeach

            @auth
                @unless (auth()->user()->isAdmin())
                    <a href="{{ route('support') }}" class="block rounded-2xl bg-stone-900 text-white p-5 hover:bg-stone-800">
                        <p class="font-bold">💬 {{ __('Chat with support') }}</p>
                        <p class="text-sm text-stone-400 mt-1">{{ __('Logged in? Message our team directly from your account.') }}</p>
                    </a>
                @endunless
            @endauth
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/70 p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-stone-900">{{ __('Send us a message') }}</h2>

            <x-flash class="mt-4" />

            <form method="POST" action="{{ route('contact.send') }}" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Your name')" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', auth()->user()?->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', auth()->user()?->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="phone" :value="__('Phone (optional)')" />
                    <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="subject" :value="__('Subject')" />
                    <select id="subject" name="subject" class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500" required>
                        @foreach ([__('Buying an animal'), __('Selling an animal'), __('Account help'), __('Report a listing'), __('Other')] as $subject)
                            <option value="{{ $subject }}" @selected(old('subject') === $subject)>{{ $subject }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="message" :value="__('Message')" />
                    <textarea id="message" name="message" rows="6" required class="block mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-primary-button class="px-6 py-3">{{ __('Send message') }}</x-primary-button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
