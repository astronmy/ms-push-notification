@php
    $tabs = [
        'api' => 'API',
        'firebase-requests' => 'Firebase requests',
        'firebase-logs' => 'Firebase logs',
    ];
@endphp
<div class="mb-4 flex flex-wrap gap-2">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.logs.index', ['type' => $key]) }}"
           class="rounded-md px-3 py-2 text-sm {{ $type === $key ? 'bg-[var(--bo-bg)] text-white' : 'border border-gray-200 bg-white text-gray-700' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
