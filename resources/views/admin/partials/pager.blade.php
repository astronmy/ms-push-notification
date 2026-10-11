<div class="mt-3 flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-gray-700">
        @if ($paginator->total() > 0)
            {{ __('admin.table.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        @else
            {{ __('admin.table.no_records') }}
        @endif
    </p>
    <div>{{ $paginator->links() }}</div>
</div>
