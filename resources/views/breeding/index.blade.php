<x-app-layout>
    @php
        $levels = [
            'excellent'  => ['banner' => 'bg-gradient-to-r from-emerald-600 to-teal-600', 'bar' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700', 'icon' => '💚'],
            'good'       => ['banner' => 'bg-gradient-to-r from-sky-600 to-emerald-600',  'bar' => 'bg-sky-500',     'pill' => 'bg-sky-50 text-sky-700',         'icon' => '👍'],
            'caution'    => ['banner' => 'bg-gradient-to-r from-amber-500 to-orange-500', 'bar' => 'bg-amber-500',   'pill' => 'bg-amber-50 text-amber-700',     'icon' => '⚠️'],
            'poor'       => ['banner' => 'bg-gradient-to-r from-orange-600 to-red-600',   'bar' => 'bg-red-500',     'pill' => 'bg-red-50 text-red-700',         'icon' => '⏳'],
            'impossible' => ['banner' => 'bg-gradient-to-r from-stone-700 to-stone-900',  'bar' => 'bg-stone-500',   'pill' => 'bg-stone-100 text-stone-700',    'icon' => '🚫'],
        ];
        $marks = [
            'pass' => ['✓', 'bg-emerald-100 text-emerald-700'],
            'warn' => ['!', 'bg-amber-100 text-amber-700'],
            'fail' => ['✕', 'bg-red-100 text-red-700'],
            'info' => ['i', 'bg-stone-100 text-stone-500'],
        ];
        $sexLabel = ['male' => __('Male'), 'female' => __('Female'), 'unknown' => __('Sex not listed')];
        $describe = fn ($x) => $x->name.' — '.collect([$x->gender !== 'unknown' ? __(ucfirst($x->gender)) : null, $x->breed, $x->age, $x->location])->filter()->join(' · ');
    @endphp

    <section class="relative overflow-hidden bg-stone-950">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 15% 20%, #d97706 0, transparent 40%), radial-gradient(circle at 85% 70%, #be185d 0, transparent 45%);"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-24 text-center">
            <x-breeding-tabs active="match" />
            <h1 class="mt-6 text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ __('Breeding Match') }}</h1>
            <p class="mt-3 text-stone-300 max-w-2xl mx-auto">{{ __('Thinking of breeding two animals? Pick any two listings and see if they make a good pair: animal type, sex, breed, age, health, size and distance, scored in seconds.') }}</p>
        </div>
    </section>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-14 pb-4 space-y-6">
        <x-flash />

        {{-- Picker --}}
        <form method="GET" action="{{ route('breeding.index') }}" class="bg-white rounded-2xl border border-stone-200/70 shadow-xl shadow-stone-200/40 p-5 sm:p-7">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ([['a', __('First animal'), $a], ['b', __('Second animal'), $b]] as [$name, $label, $selected])
                    <div>
                        <label for="pick-{{ $name }}" class="block text-sm font-bold text-stone-800">{{ $label }}</label>
                        <select id="pick-{{ $name }}" name="{{ $name }}" required class="mt-1.5 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500">
                            <option value="">{{ __('Choose an animal...') }}</option>
                            @foreach ($animals as $type => $group)
                                <optgroup label="{{ $type }}">
                                    @foreach ($group as $option)
                                        <option value="{{ $option->id }}" @selected($selected && $selected->id === $option->id)>{{ $describe($option) }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-3">
                <x-primary-button class="justify-center px-6 py-3">{{ __('Check compatibility') }}</x-primary-button>
                @if (! $a && ! $b && $compared->count() >= 2)
                    <a href="{{ route('breeding.index', ['a' => $compared[0]->id, 'b' => $compared[1]->id]) }}" class="text-sm font-semibold text-amber-700 hover:underline">
                        ⚖️ {{ __('Use my compared animals') }} ({{ $compared[0]->name }} + {{ $compared[1]->name }})
                    </a>
                @endif
                @if ($a || $b)
                    <a href="{{ route('breeding.index') }}" class="text-sm font-semibold text-stone-500 hover:text-stone-800">{{ __('Start over') }}</a>
                @endif
            </div>
            <p class="mt-3 text-xs text-stone-500">{{ __('Tip: pick just one animal and we will suggest the best partners for it.') }}</p>
        </form>

        @if ($error)
            <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm font-medium">{{ $error }}</div>
        @endif

        {{-- Result --}}
        @if ($result)
            @php $level = $levels[$result['level']]; @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ([$a, $b] as $animal)
                    @php
                        $role = $result['sire']?->id === $animal->id ? __('Sire · Male') : ($result['dam']?->id === $animal->id ? __('Dam · Female') : $sexLabel[$animal->gender]);
                    @endphp
                    <a href="{{ route('animals.show', $animal) }}" class="flex items-center gap-4 bg-white rounded-2xl border border-stone-200/70 p-4 hover:shadow-lg transition">
                        <img src="{{ $animal->imageUrl() }}" alt="" class="w-20 h-20 rounded-xl object-cover bg-stone-100 shrink-0">
                        <div class="min-w-0">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-stone-100 text-[11px] font-bold text-stone-600">{{ $role }}</span>
                            <p class="mt-1 font-extrabold text-stone-900 truncate">{{ $animal->name }}</p>
                            <p class="text-xs text-stone-500 truncate">{{ collect([$animal->breed, $animal->age, __($animal->location)])->filter()->join(' · ') }}</p>
                            <p class="text-sm font-bold text-amber-700">{{ inr($animal->price, 0) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="{{ $level['banner'] }} text-white rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-4">
                    <span class="text-4xl">{{ $level['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs uppercase tracking-wider font-bold text-white/70">{{ __('Verdict') }}</p>
                        <h2 class="text-2xl sm:text-3xl font-extrabold">{{ $result['verdict'] }}</h2>
                        <p class="mt-1 text-white/90">{{ $result['summary'] }}</p>
                    </div>
                    @if ($result['possible'])
                        <div class="text-right shrink-0">
                            <p class="text-4xl sm:text-5xl font-extrabold leading-none">{{ $result['score'] }}</p>
                            <p class="text-xs text-white/70">{{ __('out of 100') }}</p>
                        </div>
                    @endif
                </div>
                @if ($result['possible'])
                    <div class="mt-5 h-2.5 rounded-full bg-white/25 overflow-hidden">
                        <div class="h-full rounded-full bg-white" style="width: {{ $result['score'] }}%"></div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
                <h3 class="font-bold text-stone-900 px-6 pt-5 pb-3">{{ __('How we checked') }}</h3>
                <div class="divide-y divide-stone-100">
                    @foreach ($result['checks'] as $check)
                        <div class="flex items-start gap-3 px-6 py-3.5">
                            <span class="mt-0.5 w-6 h-6 rounded-full {{ $marks[$check['status']][1] }} text-xs font-extrabold flex items-center justify-center shrink-0">{{ $marks[$check['status']][0] }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-stone-900">{{ $check['label'] }}</p>
                                <p class="text-sm text-stone-600">{{ $check['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($result['offspring'])
                <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
                    <h3 class="font-bold text-stone-900">🐣 {{ __('What to expect') }}</h3>
                    <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div class="rounded-xl bg-stone-50 p-4">
                            <dt class="text-[11px] uppercase tracking-wider font-semibold text-stone-400">{{ __('Offspring') }}</dt>
                            <dd class="mt-1 font-bold text-stone-900">{{ $result['offspring']['name'] }}</dd>
                            <dd class="text-xs text-stone-500">{{ $result['offspring']['type'] }}</dd>
                        </div>
                        @if ($result['offspring']['gestation'])
                            <div class="rounded-xl bg-stone-50 p-4">
                                <dt class="text-[11px] uppercase tracking-wider font-semibold text-stone-400">{{ __('Pregnancy / incubation') }}</dt>
                                <dd class="mt-1 font-bold text-stone-900">{{ $result['offspring']['gestation'] }}</dd>
                            </div>
                        @endif
                        @if ($result['offspring']['litter'])
                            <div class="rounded-xl bg-stone-50 p-4">
                                <dt class="text-[11px] uppercase tracking-wider font-semibold text-stone-400">{{ __('Typical litter') }}</dt>
                                <dd class="mt-1 font-bold text-stone-900">{{ $result['offspring']['litter'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-stone-900 text-white rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-lg">{{ __('Ready to arrange it?') }}</p>
                        <p class="text-sm text-stone-400">{{ __('Buy the pair for') }} <span class="text-white font-bold">{{ inr($a->price + $b->price, 0) }}</span>, {{ __('or message the seller first.') }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        @auth
                            <form method="POST" action="{{ route('breeding.cart') }}">
                                @csrf
                                <input type="hidden" name="a" value="{{ $a->id }}">
                                <input type="hidden" name="b" value="{{ $b->id }}">
                                <button class="w-full bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold px-5 py-3 rounded-xl">{{ __('Add both to cart') }}</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold px-5 py-3 rounded-xl text-center">{{ __('Log in to buy the pair') }}</a>
                        @endauth
                        <a href="{{ route('animals.show', $a) }}" class="bg-white/10 hover:bg-white/20 text-white text-sm font-bold px-5 py-3 rounded-xl text-center">{{ __('View animal & message seller') }}</a>
                    </div>
                </div>
            @endif

            <p class="text-xs text-stone-500 text-center px-4">{{ __('This is general guidance based on what sellers list. Always have a vet check both animals before breeding.') }}</p>
        @endif

        {{-- Suggested partners for a single animal --}}
        @if ($a && ! $b)
            <div>
                <h2 class="text-xl font-extrabold text-stone-900">{{ __('Best partners for') }} {{ $a->name }}</h2>
                <p class="text-sm text-stone-500 mt-1">{{ __('Ranked by breeding compatibility.') }}</p>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($suggestions as $row)
                        @php $partner = $row['animal']; $r = $row['result']; @endphp
                        <a href="{{ route('breeding.index', ['a' => $a->id, 'b' => $partner->id]) }}" class="bg-white rounded-2xl border border-stone-200/70 p-4 hover:shadow-lg transition block">
                            <div class="flex items-center gap-3">
                                <img src="{{ $partner->imageUrl() }}" alt="" class="w-16 h-16 rounded-xl object-cover bg-stone-100 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-stone-900 truncate">{{ $partner->name }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ collect([$partner->gender !== 'unknown' ? __(ucfirst($partner->gender)) : null, $partner->breed, $partner->age])->filter()->join(' · ') }}</p>
                                    <p class="text-xs text-stone-400 truncate">📍 {{ __($partner->location) ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $levels[$r['level']]['pill'] }}">{{ $r['verdict'] }} · {{ $r['score'] }}</span>
                                <span class="text-sm font-semibold text-amber-700">{{ __('Check match') }} →</span>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full bg-white rounded-2xl border border-dashed border-stone-300 p-10 text-center">
                            <p class="text-4xl">🔎</p>
                            <p class="mt-3 font-semibold text-stone-800">{{ __('No compatible partners listed yet.') }}</p>
                            <p class="text-sm text-stone-500">{{ __('Try again soon, or pick a second animal yourself above.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- Empty state --}}
        @if (! $a && ! $b)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                @foreach ([
                    ['1', __('Pick two animals'), __('Any two listings, for example two dogs. Or pick one and get suggestions.')],
                    ['2', __('Get a score'), __('We check type, sex, breed, age, health, size and distance.')],
                    ['3', __('Arrange it'), __('Add both to your cart or message the seller directly.')],
                ] as [$n, $title, $text])
                    <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
                        <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center">{{ $n }}</span>
                        <p class="mt-3 font-bold text-stone-900">{{ $title }}</p>
                        <p class="text-sm text-stone-500 mt-1">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
