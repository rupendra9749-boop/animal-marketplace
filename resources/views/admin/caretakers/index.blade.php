<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Caretakers') }}</h2>
    </x-slot>

    <x-flash />

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">{{ __('Caretakers') }}</h1>
            <p class="text-sm text-stone-500 mt-1">{{ __('People who look after animals, shown to buyers within 50 km. Profiles sent in by users wait here for your approval.') }}</p>
        </div>
        <a href="{{ route('admin.caretakers.create') }}"><x-primary-button type="button">+ {{ __('Add caretaker') }}</x-primary-button></a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ([['', __('All'), $counts['all']], ['pending', __('Waiting for approval'), $counts['pending']], ['live', __('Visible'), $counts['live']]] as [$value, $label, $count])
                <a href="{{ route('admin.caretakers.index', array_filter(['status' => $value, 'search' => request('search')])) }}"
                   class="px-3.5 py-2 rounded-full text-sm font-semibold transition {{ $status === $value ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                    {{ $label }} <span class="{{ $status === $value ? 'text-amber-300' : 'text-stone-400' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <x-text-input name="search" class="w-full md:w-64" value="{{ request('search') }}" placeholder="{{ __('Search name, city or state...') }}" />
            <x-primary-button>{{ __('Search') }}</x-primary-button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[720px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Caretaker') }}</th>
                    <th class="px-5 py-3">{{ __('Location') }}</th>
                    <th class="px-5 py-3">{{ __('Looks after') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($caretakers as $caretaker)
                    <tr class="{{ $caretaker->is_active ? '' : 'bg-amber-50/50' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if ($caretaker->photoUrl())
                                    <img src="{{ $caretaker->photoUrl() }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center shrink-0">🤝</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 truncate">{{ $caretaker->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ $caretaker->rateLabel() ?? $caretaker->phone }}@if ($caretaker->user) &middot; {{ __('account') }}: {{ $caretaker->user->name }}@endif</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-700">{{ __($caretaker->city) }}<span class="block text-xs text-stone-400">{{ __($caretaker->state) }}</span></td>
                        <td class="px-5 py-3 text-stone-600">{{ $caretaker->categories->pluck('name')->map(fn ($n) => __($n))->join(', ') ?: '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $caretaker->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $caretaker->is_active ? __('Visible') : __('Waiting') }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                <form method="POST" action="{{ route('admin.caretakers.toggle', $caretaker) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg {{ $caretaker->is_active ? 'text-stone-600 hover:bg-stone-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">{{ $caretaker->is_active ? __('Hide') : __('Approve') }}</button>
                                </form>
                                <a href="{{ route('admin.caretakers.edit', $caretaker) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.caretakers.destroy', $caretaker) }}" onsubmit="return confirm('{{ __('Remove this caretaker?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <p class="text-4xl">🤝</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No caretakers here yet') }}</p>
                            <p class="text-sm text-stone-500 mt-1">{{ __('Add one, or wait for profiles to be sent in from the website.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $caretakers->links() }}
</x-admin-layout>
