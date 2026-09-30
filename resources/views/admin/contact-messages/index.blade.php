<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Contact Inbox') }}</h2>
    </x-slot>

    <x-flash />

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        <div class="divide-y divide-stone-100">
            @forelse ($messages as $message)
                <a href="{{ route('admin.contact-messages.show', $message) }}" class="flex items-start gap-4 px-6 py-4 hover:bg-stone-50">
                    <span class="mt-1.5 w-2.5 h-2.5 rounded-full shrink-0 {{ $message->read_at ? 'bg-transparent' : 'bg-amber-500' }}"></span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm {{ $message->read_at ? 'font-medium text-stone-700' : 'font-bold text-stone-900' }}">{{ $message->name }} <span class="font-normal text-stone-400">&lt;{{ $message->email }}&gt;</span></p>
                            <span class="text-xs text-stone-400 shrink-0">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm {{ $message->read_at ? 'text-stone-600' : 'font-semibold text-stone-800' }}">{{ $message->subject }}</p>
                        <p class="text-sm text-stone-500 truncate">{{ $message->message }}</p>
                    </div>
                </a>
            @empty
                <div class="px-6 py-16 text-center">
                    <p class="text-4xl">📭</p>
                    <p class="mt-3 font-semibold text-stone-700">{{ __('No contact messages yet.') }}</p>
                    <p class="text-sm text-stone-500">{{ __('Messages sent from the Contact page will appear here.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{ $messages->links() }}
</x-admin-layout>
