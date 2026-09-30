<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Animal Doctors') }}</h2>
    </x-slot>

    <x-flash />

    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-stone-900">{{ __('Animal doctors') }}</h1>
            <p class="text-sm text-stone-500 mt-1">{{ __('Doctors buyers can search by city. New clinics sent in by users wait here for your approval.') }}</p>
        </div>
        <a href="{{ route('admin.vets.create') }}"><x-primary-button type="button">+ {{ __('Add doctor') }}</x-primary-button></a>
    </div>

    {{-- AI search --}}
    <div class="bg-white rounded-2xl border border-violet-100 p-4 sm:p-5" x-data="{ open: false, busy: false }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="font-bold text-stone-900">🤖 {{ __('AI doctor search') }}</p>
                <p class="text-sm text-stone-500 mt-0.5">
                    @if ($aiConfigured)
                        {{ __('Find real clinics on the web for any city and add them here as unverified doctors. Buyers can also trigger it themselves when a search finds nobody.') }}
                    @else
                        {{ __('Not switched on yet. Add ANTHROPIC_API_KEY to the server settings (.env) to let AI search the web for doctors.') }}
                    @endif
                </p>
            </div>
            @if ($aiConfigured)
                <button type="button" @click="open = ! open" class="shrink-0 px-4 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold" x-text="open ? @js(__('Close')) : @js(__('Search a city'))"></button>
            @endif
        </div>

        @if ($aiConfigured)
            <form method="POST" action="{{ route('admin.vets.ai-search') }}" x-show="open" style="display: none;" class="mt-4 pt-4 border-t border-stone-100" @submit="busy = true">
                @csrf
                <x-location-picker />
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="ai-type" class="block font-medium text-sm text-gray-700">{{ __('Animal treated (optional)') }}</label>
                        <select id="ai-type" name="type" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">{{ __('Any animal') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" :disabled="busy" class="w-full px-4 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold disabled:opacity-60" x-text="busy ? @js(__('Searching the web... up to a minute')) : @js(__('Run AI search'))"></button>
                    </div>
                </div>
            </form>
        @endif

        @if ($aiSearches->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-stone-100">
                <p class="text-xs font-bold uppercase tracking-wider text-stone-400">{{ __('Recent AI searches') }}</p>
                <ul class="mt-2 divide-y divide-stone-100 text-sm">
                    @foreach ($aiSearches as $search)
                        <li class="py-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="font-semibold text-stone-800">{{ __($search->city) }}, {{ __($search->state) }}</span>
                            @if ($search->category)<span class="text-stone-500">{{ __($search->category->name) }}</span>@endif
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $search->status === 'ok' ? 'bg-emerald-50 text-emerald-700' : ($search->status === 'empty' ? 'bg-stone-100 text-stone-600' : 'bg-red-50 text-red-700') }}">{{ $search->status === 'ok' ? $search->found_count.' '.__('added') : ($search->status === 'empty' ? __('nothing new') : __('failed')) }}</span>
                            <span class="text-xs text-stone-400 ml-auto">{{ $search->user?->name ?? __('system') }} · {{ $search->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach ([['', __('All'), $counts['all']], ['pending', __('Waiting for approval'), $counts['pending']], ['live', __('Visible'), $counts['live']], ['ai', __('AI-found'), $counts['ai']]] as [$value, $label, $count])
                <a href="{{ route('admin.vets.index', array_filter(['status' => $value, 'search' => request('search')])) }}"
                   class="px-3.5 py-2 rounded-full text-sm font-semibold transition {{ $status === $value ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                    {{ $label }} <span class="{{ $status === $value ? 'text-amber-300' : 'text-stone-400' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2">
            @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <x-text-input name="search" class="w-full md:w-64" value="{{ request('search') }}" placeholder="{{ __('Search doctor, clinic or city...') }}" />
            <x-primary-button>{{ __('Search') }}</x-primary-button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[720px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Doctor') }}</th>
                    <th class="px-5 py-3">{{ __('City') }}</th>
                    <th class="px-5 py-3">{{ __('Treats') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($vets as $vet)
                    <tr class="{{ $vet->is_active ? '' : 'bg-amber-50/50' }}">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if ($vet->photoUrl())
                                    <img src="{{ $vet->photoUrl() }}" alt="" class="w-10 h-10 rounded-xl object-cover shrink-0">
                                @else
                                    <span class="w-10 h-10 rounded-xl bg-sky-100 text-sky-800 font-bold flex items-center justify-center shrink-0">🩺</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 truncate">{{ $vet->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ $vet->clinic_name ?: $vet->phone }}@if ($vet->submittedBy) &middot; {{ __('sent by') }} {{ $vet->submittedBy->name }}@endif</p>
                                    @if ($vet->isAiFound())
                                        <p class="text-xs mt-0.5"><span class="px-1.5 py-0.5 rounded-full font-bold {{ $vet->is_verified ? 'bg-emerald-50 text-emerald-700' : 'bg-violet-50 text-violet-700' }}">🤖 {{ $vet->is_verified ? __('AI-found, verified') : __('AI-found, unverified') }}</span>@if ($vet->source_url) <a href="{{ $vet->source_url }}" target="_blank" rel="noopener nofollow" class="text-violet-700 underline">{{ $vet->sourceHost() }}</a>@endif</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-700">{{ __($vet->city) }}</td>
                        <td class="px-5 py-3 text-stone-600">{{ $vet->categories->pluck('name')->map(fn ($n) => __($n))->join(', ') ?: '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $vet->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">{{ $vet->is_active ? __('Visible') : __('Waiting') }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                @if ($vet->isAiFound() && ! $vet->is_verified)
                                    <form method="POST" action="{{ route('admin.vets.verify', $vet) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-violet-50 text-violet-800 hover:bg-violet-100">{{ __('Mark verified') }}</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.vets.toggle', $vet) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg {{ $vet->is_active ? 'text-stone-600 hover:bg-stone-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">{{ $vet->is_active ? __('Hide') : __('Approve') }}</button>
                                </form>
                                <a href="{{ route('admin.vets.edit', $vet) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.vets.destroy', $vet) }}" onsubmit="return confirm('{{ __('Remove this doctor?') }}')">
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
                            <p class="text-4xl">🩺</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No doctors here yet') }}</p>
                            <p class="text-sm text-stone-500 mt-1">{{ __('Add a doctor, or wait for clinics to be sent in from the website.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $vets->links() }}
</x-admin-layout>
