<div class='text-end'>
    @if (hasPermission('ROLE.UPDATE'))
        <a href="{{ route($tenantRoute . 'role.edit', $role->id) }}" class="btn btn-sm btn-outline-warning"
            data-toggle="tooltip" title="Edit">
            <i class="fas fa-pencil-alt"></i>
        </a>
    @endif

    @if (hasPermission('ROLE.DELETE'))
        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
            data-id="{{ $role->id }}" data-route="{{ route($tenantRoute . 'role.destroy', $role->id) }}"
            data-toggle="tooltip" title="Delete">
            <i class="fas fa-trash-alt"></i>
        </button>
    @endif
</div>
