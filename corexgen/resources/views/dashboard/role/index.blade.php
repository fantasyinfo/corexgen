@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded ">
                @include('dashboard.role.components.role-filters')

                @include('layout.components.header-stats-new')
                @include('layout.components.bulk-import-modal')




                @if (hasPermission('ROLE.READ_ALL') || hasPermission('ROLE.READ'))

                @php

                $columns = [
                    [
                        'data' => null,
                        'label' => new \Illuminate\Support\HtmlString('<input type="checkbox" id="select-all" />'),
                        'orderable' => false,
                        'searchable' => false,
                        'width' => '10px',
                        'render' => 'function(data, type, row) {
                            return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                        }',
                    ],
                    [
                        'data' => 'role_name',
                        'name' => 'role_name',
                        'label' => __('crm_role.Role Name'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '150px',
                    ],
                    [
                        'data' => 'role_desc',
                        'name' => 'role_desc',
                        'label' => __('crm_role.Description'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    
                    [
                        'data' => 'status',
                        'name' => 'status',
                        'label' => __('crud.Status'),
                        'searchable' => false,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'created_at',
                        'name' => 'created_at',
                        'label' => __('crud.Created At'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
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

            <x-data-table id="rolesTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
                :bulk-delete-url="route(getPanelRoutes($module . '.bulkDelete'))" :csrf-token="csrf_token()" />

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



@include('layout.components.bulk-import-js')

