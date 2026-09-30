<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2>{{ $message->subject }}</h2>
            <a href="{{ route('admin.contact-messages.index') }}" class="text-sm font-semibold text-amber-700 hover:underline shrink-0">{{ __('← Inbox') }}</a>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl border border-stone-200/70 p-6 sm:p-8 max-w-3xl">
        <div class="flex items-start justify-between gap-4 pb-6 border-b border-stone-100">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($message->name, 0, 1)) }}</span>
                <div>
                    <p class="font-bold text-stone-900">{{ $message->name }}</p>
                    <p class="text-sm text-stone-500">{{ $message->email }} @if ($message->phone) · {{ $message->phone }} @endif</p>
                </div>
            </div>
            <span class="text-xs text-stone-400">{{ $message->created_at->translatedFormat('d M Y, H:i') }}</span>
        </div>

        <p class="mt-6 text-stone-700 whitespace-pre-line leading-relaxed">{{ $message->message }}</p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.$message->subject) }}">
                <x-primary-button type="button">{{ __('Reply by email') }}</x-primary-button>
            </a>
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('{{ __('Delete this message?') }}')">
                @csrf
                @method('DELETE')
                <x-danger-button type="submit">{{ __('Delete') }}</x-danger-button>
            </form>
        </div>
    </div>
</x-admin-layout>
