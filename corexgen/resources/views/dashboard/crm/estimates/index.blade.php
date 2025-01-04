@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">

            @include('layout.components.header-buttons')


            <div class="shadow-sm rounded">

                @include('dashboard.crm.estimates.components.estimate-filters')

                @include('layout.components.header-stats')



                @if (hasPermission('ESTIMATES.READ_ALL') || hasPermission('ESTIMATES.READ'))
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
                                'data' => '_id',
                                'name' => '_id',
                                'label' => __('estimates.ID'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('estimates.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'to',
                                'name' => 'to',
                                'label' => __('estimates.To'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '300px',
                            ],
                            [
                                'data' => 'value',
                                'name' => 'value',
                                'label' => __('estimates.Value'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'creating_date',
                                'name' => 'creating_date',
                                'label' => __('estimates.Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'valid_date',
                                'name' => 'valid_date',
                                'label' => __('estimates.Valid Till'),
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

                    <x-data-table id="companyEstimateTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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



