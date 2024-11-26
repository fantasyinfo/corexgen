<div class="col-md-8">
    <div class="row g-2 justify-content-end">
        @if (hasPermission('ROLE.CREATE'))
        <div class="col-12 col-md-auto">
            <a data-toggle="tooltip" data-placement="top" title="Create New"
                href="{{ route(getPanelRoutes('role.create')) }}" class="btn btn-md btn-primary w-100 mb-2 mb-md-0">
                <i class="fas fa-plus"></i> <span>{{ __('crm_role.Create Role') }}</span>
            </a>
        </div>
        @endif
        @if (hasPermission('ROLE.EXPORT'))
        <div class="col-12 col-md-auto">
            <a data-toggle="tooltip" data-placement="top" title="Export Data"
                href="{{ route(getPanelRoutes('role.export'), request()->all()) }}"
                class="btn btn-md btn-outline-secondary w-100 mb-2 mb-md-0">
                <i class="fas fa-download"></i> <span>{{ __('crud.Export') }}</span>
            </a>
        </div>
        @endif
        @if (hasPermission('ROLE.IMPORT'))
        <div class="col-12 col-md-auto">
            <button data-toggle="tooltip" data-placement="top" title="Import Data" data-bs-toggle="modal"
                data-bs-target="#bulkImportModal" class="btn btn-md btn-outline-info w-100 mb-2 mb-md-0">
                <i class="fas fa-upload"></i><span> {{ __('crud.Import') }}</span>
            </button>
        </div>
        @endif
        @if (hasPermission('ROLE.FILTER'))
        <div class="col-12 col-md-auto">
            <button data-toggle="tooltip" data-placement="top" title="Filter Data" onclick="openFilters()"
                class="btn btn-md btn-outline-warning w-100">
                <i class="fas fa-filter"></i>
                <span>{{ __('crud.Filter') }}</span>
            </button>
        </div>
        @endif
    </div>
</div>