@props(['kind', 'profile', 'unread' => 0])

{{-- Dashboard body shared by the doctor and caretaker panels. $kind is "doctor" or "caretaker". --}}
@php
    $isDoctor = $kind === 'doctor';
    $editRoute = route($kind.'.profile');
    $publicRoute = $profile && $profile->is_active ? route($isDoctor ? 'vets.show' : 'caretakers.show', $profile) : null;
    [$percent, $missing] = $profile ? $profile->completeness() : [0, []];
    $noun = $isDoctor ? __('doctor search') : __('caretaker search');
    $state = ! $profile ? 'none' : ($profile->is_active ? 'live' : 'pending');
    $title = $isDoctor ? __('Doctor Dashboard') : __('Caretaker Dashboard');
@endphp

<x-panel-layout>
    <x-slot name="header"><h2>{{ $title }}</h2></x-slot>

    <div class="rounded-2xl bg-gradient-to-r from-stone-900 to-stone-800 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-stone-400 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('Welcome,') }} {{ auth()->user()->name }} 👋</h1>
            <p class="text-stone-400 text-sm mt-1">{{ $isDoctor ? __('Keep your clinic profile complete so animal owners near you can find and call you.') : __('Keep your profile complete so animal owners near you can find and hire you.') }}</p>
        </div>
        <a href="{{ $editRoute }}" class="shrink-0 bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm px-5 py-3 rounded-xl text-center">{{ $profile ? __('Edit my profile') : __('Create my profile') }}</a>
    </div>

    {{-- Where the profile stands --}}
    @if ($state === 'none')
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="text-4xl">{{ $isDoctor ? '🩺' : '🤝' }}</span>
            <div class="flex-1">
                <p class="font-extrabold text-amber-900">{{ __('You have not created your profile yet') }}</p>
                <p class="text-sm text-amber-800 mt-0.5">{{ __('It takes about two minutes. As soon as you save it, you appear in the :noun for people within 50 km.', ['noun' => $noun]) }}</p>
            </div>
            <a href="{{ $editRoute }}" class="shrink-0 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl text-center">{{ __('Create profile') }}</a>
        </div>
    @elseif ($state === 'pending')
        <div class="rounded-2xl bg-sky-50 border border-sky-200 p-5 sm:p-6 flex items-center gap-4">
            <span class="text-4xl">⏳</span>
            <div>
                <p class="font-extrabold text-sky-900">{{ __('Your profile is hidden') }}</p>
                <p class="text-sm text-sky-800 mt-0.5">{{ __('An admin has hidden this profile, so it is not in the :noun right now. Please contact support if you think this is a mistake.', ['noun' => $noun]) }}</p>
            </div>
        </div>
    @else
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
            <span class="text-4xl">✅</span>
            <div class="flex-1">
                <p class="font-extrabold text-emerald-900">{{ __('Your profile is live') }}</p>
                <p class="text-sm text-emerald-800 mt-0.5">{{ __('People within 50 km of :city can find you.', ['city' => __($profile->city)]) }}</p>
            </div>
            <a href="{{ $publicRoute }}" class="shrink-0 bg-white border border-emerald-200 text-emerald-800 font-bold text-sm px-5 py-2.5 rounded-xl text-center hover:bg-emerald-100">{{ __('View public page') }}</a>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('messages.index') }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-stone-500">{{ __('Unread chats') }}</p>
                <span class="w-10 h-10 rounded-xl bg-violet-500 text-white flex items-center justify-center shadow-sm">💬</span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $unread }}</p>
            <p class="text-xs text-stone-500 mt-1">{{ __('from animal owners') }}</p>
        </a>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-stone-500">{{ __('Profile complete') }}</p>
                <span class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center shadow-sm">📋</span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $percent }}%</p>
            <div class="mt-2 h-2 rounded-full bg-stone-100 overflow-hidden"><div class="h-full rounded-full bg-sky-500" style="width: {{ $percent }}%"></div></div>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5 col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-stone-500">{{ __('Location') }}</p>
                <span class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-sm">📍</span>
            </div>
            <p class="mt-3 text-xl font-extrabold text-stone-900 truncate">{{ __($profile?->city ?? auth()->user()->city ?? '—') }}</p>
            <p class="text-xs text-stone-500 mt-1">{{ $profile?->state ?? auth()->user()->state }}</p>
        </div>
    </div>

    @if ($profile && $missing)
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
            <h3 class="font-bold text-stone-900">{{ __('Make your profile stand out') }}</h3>
            <p class="text-sm text-stone-500 mt-1">{{ __('Profiles with more details get more calls. Still missing:') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($missing as $item)
                    <a href="{{ $editRoute }}" class="px-3 py-1.5 rounded-full bg-amber-50 border border-amber-100 text-sm font-semibold text-amber-800 hover:bg-amber-100">+ {{ $item }}</a>
                @endforeach
            </div>
        </div>
    @endif
</x-panel-layout>
