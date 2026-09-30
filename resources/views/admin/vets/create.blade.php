<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Add Doctor') }}</h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.vets.store') }}" enctype="multipart/form-data">
            @include('vets._form', ['admin' => true])

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('admin.vets.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
                <x-primary-button>{{ __('Add doctor') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
