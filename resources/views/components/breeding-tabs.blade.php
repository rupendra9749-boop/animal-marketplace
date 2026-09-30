@props(['active' => 'browse'])

<div class="inline-flex p-1 rounded-full bg-white/10 border border-white/15 text-sm font-semibold">
    <a href="{{ route('breeding.browse') }}" class="px-4 sm:px-5 py-2 rounded-full transition {{ $active === 'browse' ? 'bg-white text-stone-900 shadow' : 'text-stone-300 hover:text-white' }}">🐾 {{ __('Find animals') }}</a>
    <a href="{{ route('breeding.index') }}" class="px-4 sm:px-5 py-2 rounded-full transition {{ $active === 'match' ? 'bg-white text-stone-900 shadow' : 'text-stone-300 hover:text-white' }}">🧬 {{ __('Check match') }}</a>
</div>
