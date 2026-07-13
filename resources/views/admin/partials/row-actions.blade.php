<div class="flex items-center justify-end gap-2">
    <a href="{{ $edit }}" class="admin-row-action admin-row-action--edit">Edit</a>
    <button type="button"
            class="admin-row-action admin-row-action--delete"
            @click="$dispatch('open-delete-modal', { action: @js($delete), name: @js($name ?? 'data ini') })">
        Hapus
    </button>
</div>
