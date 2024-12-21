@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">

            @include('layout.components.header-buttons')
            <div class="shadow-sm rounded">

                @if (hasPermission('PERMISSIONS.READ_ALL') || hasPermission('PERMISSIONS.READ'))
                    @php

                        $columns = [
                            [
                                'data' => 'role_name',
                                'name' => 'role_name',
                                'label' => __('crm_permissions.Role Name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'actions',
                                'name' => 'actions',
                                'label' => __('crud.Actions'),
                                'orderable' => false,
                                'searchable' => false,
                                'width' => '100px',
                            ],
                        ];
                    @endphp

                    <x-data-table id="permissionsTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" />
                @else
                    {{-- no permissions to view --}}
                    <div class="no-data-found">
                        <i class="fas fa-ban"></i>
                        <span class="mx-2">{{ __('crud.You do not have permission to view the table') }}</span>
                    </div>
                @endif





            </div>
        </div>
    </div>
@endsection
