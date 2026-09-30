@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['type' => 'text', 'class' => 'border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm']) }}>
