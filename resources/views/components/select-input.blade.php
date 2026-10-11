@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'mt-1 block']) }}>
    {{ $slot }}
</select>
