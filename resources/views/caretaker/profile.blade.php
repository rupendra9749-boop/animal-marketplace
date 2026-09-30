<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Service profile') }}</h2></x-slot>

    @if (! $caretaker->exists)
        <div class="max-w-3xl rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-900">
            🤝 {{ __('Tell animal owners what you offer. As soon as you save, you appear in the caretaker search for people within 50 km.') }}
        </div>
    @elseif (! $caretaker->is_active)
        <div class="max-w-3xl rounded-2xl bg-sky-50 border border-sky-200 px-5 py-4 text-sm text-sky-900">⏳ {{ __('Your profile is waiting for admin approval. You can still edit it.') }}</div>
    @endif

    <form method="POST" action="{{ route('caretaker.profile.update') }}" enctype="multipart/form-data" class="max-w-3xl">
        @method('PUT')
        @include('caretakers._form', ['admin' => false, 'withPhoto' => true])

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('caretaker.dashboard') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
            <x-primary-button class="px-6 py-3">{{ $caretaker->exists ? __('Save changes') : __('Publish my profile') }}</x-primary-button>
        </div>
    </form>
</x-panel-layout>
