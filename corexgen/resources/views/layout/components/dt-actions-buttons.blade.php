<div class="dropdown text-end">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-ellipsis-h"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @if (isset($permissions['LOGIN_AS']) && hasPermission(strtoupper($module) . '.' . $permissions['LOGIN_AS']['KEY']))
            <li class="m-1 p-1">
                <a class="dropdown-item" href="{{ route($tenantRoute . $module . '.loginas', $id) }}" data-toggle="tooltip" title="Login to view">
                    <i class="fas fa-sign-in-alt me-2"></i> Login as
                </a>
            </li>
        @endif

        @if (isset($permissions['UPDATE']) && hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']))
            <li class="m-1 p-1">
                <a class="dropdown-item" href="{{ route($tenantRoute . $module . '.edit', $id) }}" data-toggle="tooltip" title="Edit">
                    <i class="fas fa-pencil-alt me-2"></i> Edit
                </a>
            </li>
        @endif

        @if (isset($permissions['DELETE']) && hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']))
            <li class="m-1 p-1">
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $id }}" data-route="{{ route($tenantRoute . $module . '.destroy', $id) }}" data-toggle="tooltip" title="Delete">
                    <i class="fas fa-trash-alt me-2"></i> Delete
                </a>
            </li>
        @endif
    </ul>
</div>