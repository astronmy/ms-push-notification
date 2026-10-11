@props(['clearUrl'])

@php
    $active = collect(request()->except(['page', 'tab']))
        ->contains(fn ($value) => $value !== null && $value !== '');
@endphp

<div class="mb-4" x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button type="button" class="filter-toggle" @click="open = !open" :aria-expanded="open">
        <x-admin.icon name="filter" />
        {{ __('admin.actions.filters') }}
        @if ($active)
            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">{{ __('admin.actions.filters_active') }}</span>
        @endif
        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <form method="GET" x-show="open" x-cloak x-transition class="filter-panel" {{ $attributes }}>
        {{ $slot }}
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">{{ __('admin.actions.filter') }}</button>
            <a href="{{ $clearUrl }}" class="btn btn-secondary">{{ __('admin.actions.clear') }}</a>
        </div>
    </form>
</div>
