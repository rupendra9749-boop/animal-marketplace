<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Edit Doctor') }}</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.vets.update', $vet) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('vets._form', ['admin' => true])

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.vets.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                <x-primary-button>{{ __('Save changes') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
