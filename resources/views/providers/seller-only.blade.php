@props([])
<x-app-layout>
    <x-slot name="title">{{ $what === 'doctor' ? __('List your clinic') : __('Offer your care services') }}</x-slot>

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-14 text-center">
        <span class="mx-auto w-16 h-16 rounded-2xl {{ $what === 'doctor' ? 'bg-sky-50' : 'bg-emerald-50' }} text-4xl flex items-center justify-center">{{ $what === 'doctor' ? '🩺' : '🤝' }}</span>
        <h1 class="mt-5 text-2xl sm:text-3xl font-extrabold text-stone-900">{{ $what === 'doctor' ? __('Doctors list their clinic as sellers') : __('Caretakers create their profile as sellers') }}</h1>
        <p class="mt-3 text-stone-500">{{ __('A seller account can sell animals, list breeding animals, offer a doctor profile and a caretaker profile - and still buy and use services like anyone else. It is free.') }}</p>

        @auth
            <form method="POST" action="{{ route('account.become-seller') }}" class="mt-6">
                @csrf
                <x-primary-button class="justify-center px-6 py-3">{{ __('Become a seller') }}</x-primary-button>
            </form>
        @else
            <a href="{{ route('register') }}" class="mt-6 inline-block"><x-primary-button type="button" class="px-6 py-3">{{ __('Create a seller account') }}</x-primary-button></a>
            <p class="mt-3 text-sm text-stone-500">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="font-semibold text-amber-700 hover:underline">{{ __('Log in') }}</a></p>
        @endauth
    </div>
</x-app-layout>
