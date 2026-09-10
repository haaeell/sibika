<div class="flex items-center justify-end gap-1">
    <a href="{{ route('bk.cohorts.edit', $cohort) }}" class="btn-icon has-tooltip" data-tooltip="Edit" aria-label="Edit">
        <i class="fa-solid fa-pen"></i>
    </a>
    <form action="{{ route('bk.cohorts.destroy', $cohort) }}" method="POST" class="js-delete-form inline-flex">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-icon has-tooltip text-rose-600" data-tooltip="Hapus" aria-label="Hapus">
            <i class="fa-solid fa-trash"></i>
        </button>
    </form>
</div>
