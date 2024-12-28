<div class="d-flex  flex-wrap justify-content-between align-items-center">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">{{ __('navbar.Home') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $title ?? '' }}</li>
        </ol>
    </nav>
    <div class="mb-3 d-flex flex-wrap justify-content-end">


        @if (isset($permissions['CREATE']) && hasPermission(strtoupper($module) . '.' . $permissions['CREATE']['KEY']))
            <a href="{{ route(getPanelRoutes($module . '.create')) }}" class="btn btn-primary btn-xl me-2"
                data-toggle="tooltip" title="{{ __('crud.Create New') }}">
                <i class="fas fa-plus me-2"></i> {{ __('Create') }}
            </a>
        @endif

        @if (
            (isset($permissions['EXPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['EXPORT']['KEY'])) ||
                (isset($permissions['IMPORT']) && hasPermission(strtoupper($module) . '.' . $permissions['IMPORT']['KEY'])) ||
                (isset($permissions['FILTER']) && hasPermission(strtoupper($module) . '.' . $permissions['FILTER']['KEY'])) ||
                (isset($permissions['BULK_DELETE']) &&
                    hasPermission(strtoupper($module) . '.' . $permissions['BULK_DELETE']['KEY'])) ||
                (isset($permissions['KANBAN_BOARD']) &&
                    hasPermission(strtoupper($module) . '.' . $permissions['KANBAN_BOARD']['KEY'])))
            <div class="d-flex">
                {{-- showing the table and kanban board view btn --}}




                @if (isset($permissions['KANBAN_BOARD']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['KANBAN_BOARD']['KEY']))
                    @if (Route::currentRouteName() === getPanelRoutes($module . '.kanban'))
                        {{-- Show the "Table View" button when on Kanban Board --}}
                        <a class="btn btn-outline-secondary btn-xl me-2" data-toggle="tooltip"
                            href="{{ route(getPanelRoutes($module . '.index')) }}" title="{{ __('crud.Table View') }}">
                            <i class="fas fa-table"></i>
                        </a>
                    @elseif (Route::currentRouteName() === getPanelRoutes($module . '.index'))
                        {{-- Show the "Kanban Board" button when on Table View --}}
                        <a class="btn btn-outline-secondary btn-xl me-2" data-toggle="tooltip"
                            href="{{ route(getPanelRoutes($module . '.kanban')) }}"
                            title="{{ __('crud.Kanban Board') }}">
                            <i class="fas fa-columns"></i>
                        </a>
                    @endif
                @endif
                {{-- showing current user board btn --}}
                @if (isset($permissions['KANBAN_BOARD']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['KANBAN_BOARD']['KEY']))
                    @if (Route::currentRouteName() === getPanelRoutes($module . '.kanban'))
                        {{-- Show the "Table View" button when on Kanban Board --}}
                        @if (Request::get('current_user') == 'true')
                            <a class="btn btn-outline-secondary btn-xl me-2 active" href="?current_user=false"
                                title="{{ __('crud.My Results') }}" data-toggle="tooltip">
                                <i class="fas fa-user"></i>
                            </a>
                        @else
                            <a class="btn btn-outline-secondary btn-xl me-2" href="?current_user=true"
                                title="{{ __('crud.My Results') }}" data-toggle="tooltip">
                                <i class="fas fa-user"></i>
                            </a>
                        @endif
                    @elseif (Route::currentRouteName() === getPanelRoutes($module . '.index'))
                        {{-- Show the "Kanban Board" button when on Table View --}}

                        @if (Request::get('current_user') == 'true')
                            <a class="btn btn-outline-secondary btn-xl me-2 active" href="?current_user=false"
                                title="{{ __('crud.My Results') }}" data-toggle="tooltip">
                                <i class="fas fa-user"></i>
                            </a>
                        @else
                            <a class="btn btn-outline-secondary btn-xl me-2" href="?current_user=true"
                                title="{{ __('crud.My Results') }}" data-toggle="tooltip">
                                <i class="fas fa-user"></i>
                            </a>
                        @endif

                    @endif
                @endif
                @if (isset($permissions['FILTER']) && hasPermission(strtoupper($module) . '.' . $permissions['FILTER']['KEY']))
                    <a class="btn btn-outline-secondary btn-xl me-2" href="#" id="filterToggle"
                        title="{{ __('crud.Filter') }}" data-toggle="tooltip">
                        <i class="fas fa-filter"></i>
                    </a>
                @endif

                @if (isset($permissions['EXPORT']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['EXPORT']['KEY']) &&
                        Route::currentRouteName() !== getPanelRoutes($module . '.kanban'))
                    <a class="btn btn-outline-secondary btn-xl me-2"
                        href="{{ route(getPanelRoutes($module . '.export'), request()->all()) }}"
                        title="{{ __('crud.Export') }}" data-toggle="tooltip">
                        <i class="fas fa-download"></i>
                    </a>
                @endif
                @if (isset($permissions['IMPORT']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['IMPORT']['KEY']) &&
                        Route::currentRouteName() !== getPanelRoutes($module . '.kanban'))
                    <a class="btn btn-outline-secondary btn-xl me-2"
                        href="{{ route(getPanelRoutes($module . '.import'), request()->all()) }}"
                        title="{{ __('crud.Import') }}" data-toggle="tooltip">
                        <i class="fas fa-upload"></i>
                    </a>
                @endif
                @if (isset($permissions['BULK_DELETE']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['BULK_DELETE']['KEY']) &&
                        Route::currentRouteName() !== getPanelRoutes($module . '.kanban'))
                    <a class="btn btn-outline-danger btn-xl me-2" href="#" id="bulk-delete-btn"
                        title="{{ __('crud.Bulk Delete') }}" data-toggle="tooltip">
                        <i class="fas fa-trash"></i>
                    </a>
                @endif

            </div>
        @endif
    </div>
</div>
