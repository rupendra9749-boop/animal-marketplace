@auth
    @unless (auth()->user()->hasContactDetails() || request()->routeIs('profile.*'))
        <div {{ $attributes->merge(['class' => 'bg-amber-50 border-b border-amber-100 text-amber-900 text-sm px-4 py-2.5 text-center']) }}>
            📱 {{ __('Add your mobile number and city so we can show what is near you.') }}
            <a href="{{ route('profile.edit') }}" class="font-bold underline">{{ __('Complete my profile') }}</a>
        </div>
    @endunless
@endauth
