@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'mt-1 block']) }}>
