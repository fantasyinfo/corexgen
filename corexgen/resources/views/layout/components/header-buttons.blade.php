<div class="mb-3 d-flex justify-content-end">
    <a href="{{ route(getPanelRoutes($module . '.create')) }}" class="btn btn-primary btn-xl me-2">
        <i class="fas fa-plus"></i> {{ __('crud.Create New') }}
    </a>

    @if (
        (isset($permissions['EXPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['EXPORT']['KEY'])) ||
            (isset($permissions['IMPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['IMPORT']['KEY'])) ||
            (isset($permissions['FILTER']) && hasPermission(strtoupper($module) . '.' . $permissions['FILTER']['KEY'])) ||
            (isset($permissions['BULK_DELETE']) &&
                hasPermission(strtoupper($module) . '.' . $permissions['BULK_DELETE']['KEY'])))
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-xl dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-ellipsis-h me-2"></i> Bulk Action
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if (isset($permissions['FILTER']) && hasPermission(strtoupper($module) . '.' . $permissions['FILTER']['KEY']))
                    <li>
                        <a class="dropdown-item" href="#" id="filterToggle">
                            <i class="fas fa-filter me-1"></i> {{ __('crud.Filter') }}
                        </a>
                    </li>
                @endif
                @if (isset($permissions['EXPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['EXPORT']['KEY']))
                    <li>
                        <a class="dropdown-item"
                            href="{{ route(getPanelRoutes($module . '.export'), request()->all()) }}">
                            <i class="fas fa-download me-1"></i> {{ __('crud.Export') }}
                        </a>
                    </li>
                @endif
                @if (isset($permissions['IMPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['IMPORT']['KEY']))
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                            data-bs-target="#bulkImportModal">
                            <i class="fas fa-upload me-1"></i> {{ __('crud.Import') }}
                        </a>
                    </li>
                @endif

                @if (isset($permissions['BULK_DELETE']) && hasPermission(strtoupper($module) . '.' . $permissions['BULK_DELETE']['KEY']))
                    <li>
                        <a class="dropdown-item" href="#" id="bulk-delete-btn">
                            <i class="fas fa-trash me-1"></i> {{ __('crud.Bulk Delete') }}
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    @endif

</div>
