@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">

            @include('layout.components.header-buttons')


            <div class="shadow-sm rounded">

                @include('dashboard.crm.estimates.components.proposal-filters')

                @include('layout.components.header-stats')



                @if (hasPermission('PROPOSALS.READ_ALL') || hasPermission('PROPOSALS.READ'))
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
                                'label' => __('proposals.ID'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('proposals.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'to',
                                'name' => 'to',
                                'label' => __('proposals.To'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '300px',
                            ],
                            [
                                'data' => 'value',
                                'name' => 'value',
                                'label' => __('proposals.Value'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'creating_date',
                                'name' => 'creating_date',
                                'label' => __('proposals.Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'valid_date',
                                'name' => 'valid_date',
                                'label' => __('proposals.Valid Till'),
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

                    <x-data-table id="companyProposalTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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



