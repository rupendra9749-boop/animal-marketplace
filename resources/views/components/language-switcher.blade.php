{{-- English / Hindi. Each language is written in its own script, so it can be found from either language. --}}
@props(['dark' => false])

@php $current = app()->getLocale(); @endphp

<div {{ $attributes->class(['inline-flex items-center rounded-lg border p-0.5 text-xs font-bold leading-none', $dark ? 'border-stone-700 bg-stone-900' : 'border-stone-200 bg-white']) }} role="group" aria-label="{{ __('Language') }}">
    @foreach (config('app.locales') as $code => $name)
        <a href="{{ route('language.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
           @if ($code === $current) aria-current="true" @endif
           class="px-2.5 py-1.5 rounded-md whitespace-nowrap transition {{ $code === $current
                ? 'bg-amber-600 text-white'
                : ($dark ? 'text-stone-400 hover:text-white' : 'text-stone-600 hover:bg-stone-100') }}">{{ $name }}</a>
    @endforeach
</div>
