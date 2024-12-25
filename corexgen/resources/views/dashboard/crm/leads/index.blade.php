@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                @include('dashboard.crm.leads.components.leads-filters')
                @include('layout.components.bulk-import-modal')

                @include('layout.components.header-stats')


                @if (hasPermission('LEADS.READ_ALL') || hasPermission('LEADS.READ'))
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
                                'data' => 'type',
                                'name' => 'type',
                                'label' => __('leads.Type'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'company_name',
                                'name' => 'company_name',
                                'label' => __('leads.Company Name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('leads.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '40px',
                            ],
                            [
                                'data' => 'first_name',
                                'name' => 'first_name',
                                'label' => __('leads.First Name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'middle_name',
                                'name' => 'middle_name',
                                'label' => __('leads.Middle Name'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'last_name',
                                'name' => 'last_name',
                                'label' => __('leads.Last Name'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'email',
                                'name' => 'email',
                                'label' => __('leads.Email'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'phone',
                                'name' => 'phone',
                                'label' => __('leads.Phone'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'address',
                                'name' => 'address',
                                'label' => __('leads.Address'),
                                'searchable' => false,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'category_name',
                                'name' => 'category_group_tag.name',
                                'label' => __('leads.Category'),
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

                    <x-data-table id="companyLeadsTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" :is-checkbox="true"
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
