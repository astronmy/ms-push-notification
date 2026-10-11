@props(['status'])

@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $label = match (true) {
        $status instanceof \BackedEnum && method_exists($status, 'label') => $status->label(),
        $value === 'active' => 'Activo',
        $value === 'inactive' => 'Inactivo',
        default => $value,
    };
    $classes = match ($value) {
        'scheduled' => 'bg-sky-100 text-sky-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'processing' => 'bg-indigo-100 text-indigo-800',
        'sent', 'active' => 'bg-green-100 text-green-800',
        'failed', 'inactive' => 'bg-red-100 text-red-800',
        'cancelled' => 'bg-gray-200 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium '.$classes]) }}>
    {{ $label }}
</span>
