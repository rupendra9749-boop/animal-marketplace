<x-app-layout>
    <x-slot name="title">{{ __('Offer your care services') }}</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <a href="{{ route('caretakers.index') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">← {{ __('Back to caretakers') }}</a>
        <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-stone-900">🤝 {{ __('Offer your care services') }}</h1>
        <p class="mt-2 text-stone-500">{{ __('Fill in your details. As soon as you save, your profile appears in the caretaker search for animal owners near you.') }}</p>

        <form method="POST" action="{{ route('caretakers.store') }}" class="mt-6" enctype="multipart/form-data">
            @include('caretakers._form', ['caretaker' => new \App\Models\Caretaker(['available' => true, 'rate_unit' => 'day']), 'admin' => false])

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('caretakers.index') }}" class="text-center px-4 py-3 text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Cancel') }}</a>
                <x-primary-button class="justify-center px-6 py-3">{{ __('Publish my profile') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
