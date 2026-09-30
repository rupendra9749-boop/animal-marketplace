<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Animal Types') }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('Animal types')" :subtitle="__('The categories buyers browse by. Doctors and listings both use these.')">
        <x-slot name="actions">
            <a href="{{ route('admin.categories.create') }}"><x-primary-button type="button">+ {{ __('Add type') }}</x-primary-button></a>
        </x-slot>
    </x-admin.intro>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[480px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Type') }}</th>
                    <th class="px-5 py-3">{{ __('Animals') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/animals/'.\Illuminate\Support\Str::slug($category->name).'.svg') }}" alt="" class="w-11 h-11 rounded-xl bg-stone-100 shrink-0" onerror="this.style.visibility='hidden'">
                                <span class="font-bold text-stone-900">{{ __($category->name) }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full bg-stone-100 text-xs font-bold text-stone-600">{{ $category->animals_count }} {{ __('listed') }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('{{ __('Delete this type?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-14 text-center">
                            <p class="text-4xl">🏷️</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No animal types yet') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $categories->links() }}
</x-admin-layout>
