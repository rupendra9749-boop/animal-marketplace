@php $area = $area ?? 'seller'; @endphp
<x-panel-layout>
    <x-slot name="back">{{ route($area.'.animals.index') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2 class="truncate">{{ __('Edit') }}: {{ $animal->name }}</h2>
        </div>
    </x-slot>

    <form method="POST" action="{{ route($area.'.animals.update', $animal) }}" enctype="multipart/form-data" class="max-w-3xl">
        @method('PUT')
        @include('seller.animals._form')

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route($area.'.animals.index') }}"><x-secondary-button type="button">{{ __('Cancel') }}</x-secondary-button></a>
            <x-primary-button class="px-6 py-3">{{ __('Save changes') }}</x-primary-button>
        </div>
    </form>
</x-panel-layout>
