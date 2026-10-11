@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-gray-300 focus:border-[var(--bo-bg-elevated)] focus:ring-[var(--bo-bg-elevated)] rounded-md shadow-sm']) }}>
