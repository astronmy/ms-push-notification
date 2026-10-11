<div x-data="{
        open: false,
        confirmLabel: '',
        confirmValue: '',
        formId: '',
        openModal(label, fid) { this.confirmLabel = label; this.formId = fid; this.confirmValue = ''; this.open = true; },
        close() { this.open = false; this.confirmValue = ''; },
        submit() { if (this.confirmValue === this.confirmLabel && this.formId) { var f = document.getElementById(this.formId); if (f) f.submit(); } this.close(); }
    }"
    @open-delete-modal.window="openModal($event.detail.label, $event.detail.form)">
    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50" @click="close()"></div>
        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl" @click.stop>
            <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.delete_modal.title') }}</h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('admin.delete_modal.description_prefix') }}
                <strong x-text="confirmLabel" class="break-all"></strong>
                {{ __('admin.delete_modal.description_suffix') }}
            </p>
            <input type="text" x-model="confirmValue" class="mt-3 block w-full rounded-md border border-gray-300 px-3.5 py-2.5 text-sm" autocomplete="off">
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" @click="close()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700">{{ __('admin.actions.cancel') }}</button>
                <button type="button" @click="submit()" :disabled="confirmValue !== confirmLabel" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">{{ __('admin.actions.delete') }}</button>
            </div>
        </div>
    </div>
</div>
