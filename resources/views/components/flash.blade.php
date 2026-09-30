@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium']) }}>
        <span>{{ session('status') }}</span>
        <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800">&times;</button>
    </div>
@endif
