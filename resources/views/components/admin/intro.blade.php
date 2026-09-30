@props(['title', 'subtitle' => null])

<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div class="min-w-0">
        <h1 class="text-2xl font-extrabold text-stone-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-sm text-stone-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && ! $actions->isEmpty())
        <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
    @endif
</div>
