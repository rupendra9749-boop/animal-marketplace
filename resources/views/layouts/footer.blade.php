<footer class="bg-stone-950 text-stone-400 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="col-span-2">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center">
                    <x-application-logo class="h-5 w-5 fill-current text-white" />
                </span>
                <span class="font-extrabold text-lg text-white tracking-tight">{{ config('app.name') }}</span>
            </a>
            <p class="mt-4 text-sm max-w-sm leading-relaxed">
                {{ __('India\'s friendly marketplace for healthy livestock and pets. Buy and sell cows, buffaloes, goats, dogs, birds and more — directly from sellers near you.') }}
            </p>
        </div>

        <div>
            <h4 class="text-white font-semibold text-sm mb-3">{{ __('Marketplace') }}</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('home') }}" class="hover:text-white">{{ __('Browse Animals') }}</a></li>
                <li><a href="{{ route('compare.index') }}" class="hover:text-white">{{ __('Compare') }}</a></li>
                <li><a href="{{ route('breeding.browse') }}" class="hover:text-white">{{ __('Breeding Animals') }}</a></li>
                <li><a href="{{ route('breeding.index') }}" class="hover:text-white">{{ __('Breeding Match') }}</a></li>
                <li><a href="{{ route('vets.index') }}" class="hover:text-white">{{ __('Find a Vet') }}</a></li>
                <li><a href="{{ route('caretakers.index') }}" class="hover:text-white">{{ __('Find a Caretaker') }}</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-white">{{ __('Sell an Animal') }}</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-white font-semibold text-sm mb-3">{{ __('Company') }}</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('about') }}" class="hover:text-white">{{ __('About Us') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('Contact') }}</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('Privacy policy') }}</a></li>
                <li><a href="{{ route('account.deletion') }}" class="hover:text-white">{{ __('Delete your account') }}</a></li>
                <li><a href="{{ route('vets.create') }}" class="hover:text-white">{{ __('List your clinic') }}</a></li>
                <li><a href="{{ route('caretakers.create') }}" class="hover:text-white">{{ __('Offer care services') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-xs text-stone-500 flex flex-col sm:flex-row justify-between gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</span>
            <x-language-switcher dark class="self-start" />
            <span>{{ __('Made with care for farmers and pet lovers.') }} &middot; {{ __('Location data: countries-states-cities-database (ODbL)') }}</span>
        </div>
    </div>
</footer>
