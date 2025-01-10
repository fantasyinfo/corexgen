@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                @include('dashboard.crm.tasks.components.tasks-filters')
                @include('layout.components.header-stats-new')


                @if (hasPermission('TASKS.READ_ALL') || hasPermission('TASKS.READ'))
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
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('tasks.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'related_to',
                                'name' => 'related_to',
                                'label' => __('tasks.Related To'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],

                            [
                                'data' => 'assign_to',
                                'name' => 'assign_to',
                                'label' => __('tasks.Assign To'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '200px',
                            ],

                            [
                                'data' => 'stage',
                                'name' => 'stage.name',
                                'label' => __('tasks.Stage'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
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

                    <x-data-table id="companyTasksTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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
