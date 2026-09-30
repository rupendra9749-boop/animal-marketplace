<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Add Animal Type') }}</h2>
    </x-slot>

    <div class="max-w-lg space-y-4">
        <x-admin.intro :title="__('Add an animal type')" :subtitle="__('For example Camel or Rabbit. It appears in filters, listings and the doctor search.')" />

        <div class="bg-white rounded-2xl border border-stone-200/70 p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf

                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" value="{{ old('name') }}" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.categories.index') }}">
                        <x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button>
                    </a>
                    <x-primary-button>{{ __('Create') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
