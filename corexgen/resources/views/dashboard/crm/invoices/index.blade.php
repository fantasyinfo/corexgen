@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">

            @include('layout.components.header-buttons')


            <div class="shadow-sm rounded">

                @include('dashboard.crm.invoices.components.invoice-filters')

                @include('layout.components.header-stats-new')



                @if (hasPermission('INVOICES.READ_ALL') || hasPermission('INVOICES.READ'))
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
                                'label' => __('invoices.ID'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                           
                            [
                                'data' => 'to',
                                'name' => 'to',
                                'label' => __('invoices.To'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '300px',
                            ],
                            [
                                'data' => 'task',
                                'name' => 'task',
                                'label' => __('invoices.Task'),
                                'searchable' => false,
                                'orderable' => false,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'total_amount',
                                'name' => 'total_amount',
                                'label' => __('invoices.Total Amount'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'issue_date',
                                'name' => 'issue_date',
                                'label' => __('invoices.Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'due_date',
                                'name' => 'due_date',
                                'label' => __('invoices.Due Date'),
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

                    <x-data-table id="companyInvoiceTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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



