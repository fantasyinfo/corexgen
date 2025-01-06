@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                @include('dashboard.crm.projects.components.projects-filters')
                @include('layout.components.bulk-import-modal')

                @include('layout.components.header-stats')


                @if (hasPermission('PROJECTS.READ_ALL') || hasPermission('PROJECTS.READ'))
                    @php

                        $columns = [
                            [
                                'data' => null,
                                'label' => new \Illuminate\Support\HtmlString(
                                    '<input type="checkbox" id="select-all" />',
                                ),
                                'orderable' => false,
                                'searchable' => false,
                                'width' => '10px',
                                'render' => 'function(data, type, row) {
                                    return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                                }',
                            ],
                            [
                                'data' => 'billing_type',
                                'name' => 'billing_type',
                                'label' => __('projects.Billing Type'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],

                            [
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('projects.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'client_name',
                                'name' => 'client_name',
                                'label' => __('projects.Client'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'assign_to',
                                'name' => 'assign_to',
                                'label' => __('projects.Assign To'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '200px',
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
                                'width' => '200px',
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

                    <x-data-table id="companyProjectsTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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
