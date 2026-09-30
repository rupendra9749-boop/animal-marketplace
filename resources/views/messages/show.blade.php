@php $other = $conversation->otherParticipant(auth()->user()); @endphp
<x-dynamic-component :component="auth()->user()->isAdmin() ? 'admin-layout' : 'panel-layout'">
    <x-slot name="header">
        <div class="flex items-center gap-3 min-w-0">
            <span class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($other->name, 0, 1)) }}</span>
            <h2 class="truncate">{{ $other->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        @if ($conversation->animal)
            <a href="{{ route('animals.show', $conversation->animal) }}" class="flex items-center gap-4 bg-white rounded-2xl border border-stone-200/70 p-3 hover:shadow-md transition">
                <img src="{{ $conversation->animal->imageUrl() }}" class="w-14 h-14 rounded-xl object-cover bg-stone-100" alt="">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] uppercase tracking-wider font-semibold text-stone-400">{{ __('About this animal') }}</p>
                    <p class="font-bold text-stone-900 truncate">{{ $conversation->animal->name }}</p>
                </div>
                <p class="font-extrabold text-amber-700">{{ inr($conversation->animal->price, 0) }}</p>
            </a>
        @endif

        <div class="bg-stone-100/70 rounded-2xl border border-stone-200/70 p-4 sm:p-5 space-y-3 max-h-[26rem] overflow-y-auto" x-data x-init="$el.scrollTop = $el.scrollHeight">
            @forelse ($conversation->messages as $message)
                @php $mine = $message->sender_id === auth()->id(); @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%]">
                        <div class="px-4 py-2.5 text-sm whitespace-pre-line shadow-sm {{ $mine ? 'bg-amber-600 text-white rounded-2xl rounded-br-md' : 'bg-white text-stone-800 rounded-2xl rounded-bl-md' }}">{{ $message->body }}</div>
                        <p class="mt-1 text-[11px] text-stone-400 {{ $mine ? 'text-right' : '' }}">{{ $message->created_at->translatedFormat('d M, H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-stone-500 text-center py-6">{{ __('Say hello to start the conversation.') }}</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="bg-white rounded-2xl border border-stone-200/70 p-3 flex items-end gap-3">
            @csrf
            <textarea name="body" rows="2" required class="flex-1 border-0 focus:ring-0 resize-none text-sm" placeholder="{{ __('Write a message...') }}"></textarea>
            <x-primary-button class="shrink-0">{{ __('Send') }}</x-primary-button>
        </form>
        <x-input-error :messages="$errors->get('body')" />
    </div>
</x-dynamic-component>
