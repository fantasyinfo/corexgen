<div class="col-md-8">
    <div class="row g-2 justify-content-end">
        @if (isset($permissions['CREATE']) && hasPermission(strtoupper($module) . '.' .$permissions['CREATE']['KEY']))
            <div class="col-12 col-md-auto">
                <a data-toggle="tooltip" data-placement="top" title="Create New"
                    href="{{ route(getPanelRoutes($module . '.create')) }}" class="btn btn-md btn-primary w-100 mb-2 mb-md-0">
                    <i class="fas fa-plus"></i> <span>{{ __('crud.Create New') }}</span>
                </a>
            </div>
        @endif
        @if (isset($permissions['EXPORT']) && hasPermission(strtoupper($module) . '.' .$permissions['EXPORT']['KEY']))
            <div class="col-12 col-md-auto">
                <a data-toggle="tooltip" data-placement="top" title="Export Data"
                    href="{{ route(getPanelRoutes($module .'.export'), request()->all()) }}"
                    class="btn btn-md btn-outline-secondary w-100 mb-2 mb-md-0">
                    <i class="fas fa-download"></i> <span>{{ __('crud.Export') }}</span>
                </a>
            </div>
        @endif
        @if (isset($permissions['IMPORT']) && hasPermission(strtoupper($module) . '.' .$permissions['IMPORT']['KEY']))
            <div class="col-12 col-md-auto">
                <button data-toggle="tooltip" data-placement="top" title="Import Data" data-bs-toggle="modal"
                    data-bs-target="#bulkImportModal" class="btn btn-md btn-outline-info w-100 mb-2 mb-md-0">
                    <i class="fas fa-upload"></i><span> {{ __('crud.Import') }}</span>
                </button>
            </div>
        @endif
        @if (isset($permissions['FILTER']) && hasPermission(strtoupper($module) . '.' .$permissions['FILTER']['KEY']))
            <div class="col-12 col-md-auto">
                <button data-toggle="tooltip" data-placement="top" title="Filter Data" id="filterToggle"
                    class="btn btn-md btn-outline-warning w-100">
                    <i class="fas fa-filter"></i>
                    <span>{{ __('crud.Filter') }}</span>
                </button>
            </div>
        @endif
        @if (isset($permissions['BULK_DELETE']) && hasPermission(strtoupper($module) . '.' .$permissions['BULK_DELETE']['KEY']))
            <div class="col-12 col-md-auto">
                <button data-toggle="tooltip" data-placement="top" title="Bulk Delete Data" id="bulk-delete-btn"
                    class="btn btn-md btn-outline-danger w-100">
                    <i class="fas fa-trash"></i>
                    <span>{{ __('crud.Bulk Delete') }}</span>
                </button>
              
            </div>
        @endif
    </div>
</div>
