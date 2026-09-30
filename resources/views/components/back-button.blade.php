{{-- A visible "back" arrow. The app and the installed web app have no browser bar, so there is nothing else to go back with.
     resources/js/app.js (initBackButtons) shows it. With no address it goes to the page before this one (or home when there is
     none); a page that names its own address ($fallback) always goes there - "back" from an order should be My Orders, not the checkout. --}}
@props(['fallback' => null])

@unless (request()->routeIs('home', 'login', 'register', '*.dashboard'))
    <button type="button" data-back="{{ $fallback ?: route('home') }}" @if ($fallback) data-explicit @endif aria-label="{{ __('Back') }}"
            {{ $attributes->class(['hidden shrink-0 w-10 h-10 rounded-full items-center justify-center text-stone-700 hover:bg-stone-100 active:bg-stone-200']) }}>
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
@endunless
