@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            <div class="d-flex  flex-wrap justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">{{ __('navbar.Home') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title ?? '' }}</li>
                    </ol>
                </nav>
                <div class="mb-3 d-flex flex-wrap justify-content-end">
                    @if (isset($permissions['CREATE']) && hasPermission(strtoupper($module) . '.' . $permissions['CREATE']['KEY']))
                        <a href="{{ route(getPanelRoutes($module . '.createProposals')) }}" class="btn btn-primary btn-xl me-2"
                            data-toggle="tooltip" title="{{ __('crud.Create New') }}">
                            <i class="fas fa-plus me-2"></i> {{ __('Create') }}
                        </a>
                    @endif

                </div>
            </div>

            <div class="shadow-sm rounded">


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
                                'width' => '200px',
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
                                'data' => 'group',
                                'name' => 'group.name',
                                'label' => __('leads.Groups'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'source',
                                'name' => 'source.name',
                                'label' => __('leads.Sources'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'stage',
                                'name' => 'stage.name',
                                'label' => __('leads.Stage'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            // [
                            //     'data' => 'status',
                            //     'name' => 'status',
                            //     'label' => __('crud.Status'),
                            //     'searchable' => false,
                            //     'orderable' => true,
                            //     'width' => '100px',
                            // ],
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

                    <x-data-table id="companyProposalsTemplateTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.indexProposals'))" :is-checkbox="true"
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
