<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-transparent bg-[var(--bo-bg)] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-[var(--bo-bg-elevated)]']) }}>
    {{ $slot }}
</button>
