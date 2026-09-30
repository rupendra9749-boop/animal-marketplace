<x-dynamic-component :component="auth()->user()->isAdmin() ? 'admin-layout' : 'panel-layout'">
    <x-slot name="header"><h2>{{ auth()->user()->isSeller() && session('panel') === 'seller' ? __('Chats') : __('Messages') }}</h2></x-slot>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        @forelse ($conversations as $conversation)
            @php
                $other = $conversation->otherParticipant(auth()->user());
                $last = $conversation->messages->first();
                $unread = $last && $last->sender_id !== auth()->id() && ! $last->read_at;
            @endphp
            <a href="{{ route('messages.show', $conversation) }}" class="flex items-center gap-4 px-5 sm:px-6 py-4 hover:bg-stone-50 border-b border-stone-100 last:border-0">
                <div class="relative shrink-0">
                    @if ($conversation->animal)
                        <img src="{{ $conversation->animal->imageUrl() }}" class="w-12 h-12 rounded-xl object-cover bg-stone-100" alt="">
                    @else
                        <span class="w-12 h-12 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</span>
                    @endif
                    @if ($unread)<span class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-amber-600 ring-2 ring-white"></span>@endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-3">
                        <p class="{{ $unread ? 'font-extrabold' : 'font-semibold' }} text-stone-900 truncate">
                            {{ $other->name }}
                            @if ($other->isAdmin())<span class="ml-1 text-[11px] font-bold text-amber-700">{{ __('SUPPORT') }}</span>@endif
                        </p>
                        <span class="text-[11px] text-stone-400 shrink-0">{{ $conversation->updated_at->diffForHumans(null, true, true) }}</span>
                    </div>
                    <p class="text-xs text-stone-500 truncate">{{ $conversation->animal?->name ?? __('Direct message') }}</p>
                    @if ($last)
                        <p class="text-sm {{ $unread ? 'text-stone-800 font-medium' : 'text-stone-500' }} truncate">{{ $last->sender_id === auth()->id() ? __('You: ') : '' }}{{ $last->body }}</p>
                    @endif
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <p class="text-5xl">💬</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('No conversations yet') }}</h3>
                <p class="text-sm text-stone-500 mt-1">{{ __('Message a seller from any animal page to start chatting.') }}</p>
            </div>
        @endforelse
    </div>
</x-dynamic-component>
