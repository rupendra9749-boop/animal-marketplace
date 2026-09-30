<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Users & roles') }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('Users & roles')" :subtitle="$users->total().' '.__('people. A seller can sell animals, list breeding animals and be a doctor and a caretaker. An admin can do everything. Everyone can browse, buy and take services.')">
        <x-slot name="actions">
            <a href="{{ route('admin.sellers.create') }}"><x-primary-button type="button">+ {{ __('Add seller') }}</x-primary-button></a>
        </x-slot>
    </x-admin.intro>

    <div class="bg-white rounded-2xl border border-stone-200/70 p-3 sm:p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @foreach (['' => __('Everyone'), 'buyer' => __('Buyers'), 'seller' => __('Sellers'), 'admin' => __('Admins')] as $value => $label)
                <a href="{{ route('admin.users.index', array_filter(['role' => $value, 'search' => request('search')])) }}"
                   class="px-3.5 py-2 rounded-full text-sm font-semibold transition {{ request('role', '') === $value ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex gap-2">
            @if (request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif
            <x-text-input name="search" class="w-full lg:w-64" value="{{ request('search') }}" placeholder="{{ __('Search name, email, phone or city...') }}" />
            <x-primary-button>{{ __('Search') }}</x-primary-button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[860px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Person') }}</th>
                    <th class="px-5 py-3">{{ __('Location') }}</th>
                    <th class="px-5 py-3">{{ __('Roles') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-full bg-stone-900 text-white font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ $user->email }}</p>
                                    <p class="text-xs text-stone-400 truncate">{{ $user->phone ?: __('No phone yet') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-700">
                            @if ($user->city)
                                {{ __($user->city) }}<span class="block text-xs text-stone-400">{{ __($user->state) }}</span>
                            @else
                                <span class="text-stone-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid grid-cols-2 gap-x-4 gap-y-1.5 w-44" onchange="this.submit()">
                                @csrf
                                @method('PATCH')
                                @foreach (['is_seller' => __('Seller'), 'is_admin' => __('Admin')] as $field => $label)
                                    <label class="flex items-center gap-1.5 text-xs font-semibold text-stone-600 cursor-pointer">
                                        <input type="checkbox" name="{{ $field }}" value="1" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500" @checked($user->{$field}) @disabled($field === 'is_admin' && $user->id === auth()->id())>
                                        {{ $label }}
                                    </label>
                                @endforeach
                                @if ($user->id === auth()->id())
                                    <input type="hidden" name="is_admin" value="1">
                                @endif
                                <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                @csrf
                                @method('PATCH')
                                @foreach (['is_seller', 'is_admin'] as $field)
                                    <input type="hidden" name="{{ $field }}" value="{{ $user->{$field} ? 1 : 0 }}">
                                @endforeach
                                <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                <button type="submit" @disabled($user->id === auth()->id()) title="{{ $user->is_active ? __('Click to suspend') : __('Click to re-activate') }}" class="px-2.5 py-1 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-red-50 text-red-700 hover:bg-red-100' }} disabled:opacity-60">
                                    {{ $user->is_active ? __('Active') : __('Suspended') }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 font-semibold">
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('messages.direct', $user) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100">💬 {{ __('Message') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('Delete this user?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <p class="text-4xl">👥</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('Nobody here') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</x-admin-layout>
