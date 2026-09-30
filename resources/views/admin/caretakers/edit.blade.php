<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Edit Caretaker') }}</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.caretakers.update', $caretaker) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('caretakers._form', ['admin' => true])

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.caretakers.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                <x-primary-button>{{ __('Save changes') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
