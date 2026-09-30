<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Breeding') }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('Breeding')" :subtitle="__('Every animal offered for breeding, who offers it, and how the module is doing. Owners find these within 250 km.')">
        <x-slot name="actions">
            <a href="{{ route('breeding.browse') }}" target="_blank"><x-secondary-button type="button">{{ __('View public page') }}</x-secondary-button></a>
        </x-slot>
    </x-admin.intro>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach ([
            [__('Breeding listings'), $stats['total'], $stats['live'].' '.__('visible'), '🧬', 'bg-rose-500'],
            [__('Studs (male)'), $stats['studs'], __('offered'), '♂', 'bg-sky-500'],
            [__('Dams (female)'), $stats['dams'], __('offered'), '♀', 'bg-amber-500'],
            [__('Breeders'), $stats['breeders'], __('with a breeder panel'), '👥', 'bg-violet-500'],
            [__('Average fee'), $stats['average_fee'] > 0 ? inr($stats['average_fee']) : '—', __('per service'), '₹', 'bg-emerald-500'],
        ] as [$label, $value, $sub, $glyph, $color])
            <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-500">{{ $label }}</p>
                    <span class="w-10 h-10 rounded-xl {{ $color }} text-white flex items-center justify-center shadow-sm text-lg font-bold">{{ $glyph }}</span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $value }}</p>
                <p class="text-xs text-stone-500 mt-1">{{ $sub }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 p-3 sm:p-4">
        <form method="GET" class="flex flex-col md:flex-row gap-3 md:items-center">
            <x-text-input name="search" class="w-full md:w-64" value="{{ request('search') }}" placeholder="{{ __('Search name, breed or city...') }}" />
            <select name="type" class="border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">{{ __('All animals') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('type') == $category->id)>{{ __($category->name) }}</option>
                @endforeach
            </select>
            <select name="sex" class="border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">{{ __('Male or female') }}</option>
                <option value="male" @selected(request('sex') === 'male')>♂ {{ __('Stud') }}</option>
                <option value="female" @selected(request('sex') === 'female')>♀ {{ __('Dam') }}</option>
            </select>
            <select name="status" class="border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                <option value="">{{ __('Visible and hidden') }}</option>
                <option value="hidden" @selected(request('status') === 'hidden')>{{ __('Hidden only') }}</option>
            </select>
            <x-primary-button>{{ __('Filter') }}</x-primary-button>
            @if (request()->hasAny(['search', 'type', 'sex', 'status']))
                <a href="{{ route('admin.breeding.index') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[760px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Animal') }}</th>
                    <th class="px-5 py-3">{{ __('Owner') }}</th>
                    <th class="px-5 py-3">{{ __('Location') }}</th>
                    <th class="px-5 py-3">{{ __('Fee') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($animals as $animal)
                    <tr>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.animals.show', $animal) }}" class="flex items-center gap-3 min-w-0">
                                <img src="{{ $animal->imageUrl() }}" alt="" class="w-11 h-11 rounded-xl object-cover bg-stone-100 shrink-0">
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 truncate hover:text-amber-700">{{ $animal->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">
                                        {{ collect([__($animal->category?->name), $animal->breed, $animal->age])->filter()->join(' · ') }}
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $animal->gender === 'male' ? 'bg-sky-50 text-sky-700' : 'bg-rose-50 text-rose-700' }}">{{ $animal->gender === 'male' ? '♂ '.__('Stud') : ($animal->gender === 'female' ? '♀ '.__('Dam') : __('Sex not listed')) }}</span>
                                    </p>
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3 text-stone-700">{{ $animal->seller->name }}<span class="block text-xs text-stone-400">{{ $animal->isBreedingOnly() ? __('Breeding only') : __('Also for sale') }}</span></td>
                        <td class="px-5 py-3 text-stone-700">{{ __($animal->location) ?? '—' }}<span class="block text-xs text-stone-400">{{ __($animal->state) }}</span></td>
                        <td class="px-5 py-3 font-semibold text-stone-900">{{ (float) $animal->breeding_fee > 0 ? inr($animal->breeding_fee) : __('Ask owner') }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.animals.update', $animal) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $animal->is_active ? 0 : 1 }}">
                                <button type="submit" title="{{ $animal->is_active ? __('Click to hide') : __('Click to show') }}" class="px-2.5 py-1 rounded-full text-xs font-bold {{ $animal->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 hover:bg-stone-200' }}">{{ $animal->is_active ? __('Visible') : __('Hidden') }}</button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                <a href="{{ route('admin.animals.show', $animal) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('View') }}</a>
                                <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" onsubmit="return confirm('{{ __('Delete this animal listing?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center">
                            <p class="text-4xl">🧬</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No breeding animals found') }}</p>
                            <p class="text-sm text-stone-500 mt-1">{{ __('Sellers and breeders add them from their panels.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $animals->links() }}
</x-admin-layout>
