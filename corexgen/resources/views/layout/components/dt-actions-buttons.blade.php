<div class='text-end'>
    @if (isset($permissions['VIEW']) && hasPermission(strtoupper($module) . '.' . $permissions['VIEW']['KEY']))
        <a href="{{ route($tenantRoute . $module . '.view', $id) }}" class="btn btn-sm btn-outline-info"
            data-toggle="tooltip" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endif

    @if (isset($permissions['UPDATE']) && hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']))
        <a href="{{ route($tenantRoute . $module . '.edit', $id) }}" class="btn btn-sm btn-outline-warning"
            data-toggle="tooltip" title="Edit">
            <i class="fas fa-pencil-alt"></i>
        </a>
    @endif

    @if (isset($permissions['DELETE']) && hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']))
        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
            data-bs-target="#deleteModal" data-id="{{ $id }}"
            data-route="{{ route($tenantRoute . $module . '.destroy', $id) }}" data-toggle="tooltip" title="Delete">
            <i class="fas fa-trash-alt"></i>
        </button>
    @endif
</div>
