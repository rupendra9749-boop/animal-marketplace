<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Profile') }}</h2></x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="bg-white rounded-2xl border border-red-100 p-5 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-panel-layout>
