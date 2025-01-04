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
                        <a href="{{ route(getPanelRoutes($module . '.create' . $type)) }}" class="btn btn-primary btn-xl me-2"
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
                                'data' => 'title',
                                'name' => 'title',
                                'label' => __('templates.Title'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            
                            [
                                'data' => 'created_by',
                                'name' => 'created_by',
                                'label' => __('templates.Created By'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '70px',
                            ],
                          
                            [
                                'data' => 'created_at',
                                'name' => 'created_at',
                                'label' => __('crud.Created At'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '70px',
                            ],
                            [
                                'data' => 'actions',
                                'name' => 'actions',
                                'label' => __('crud.Actions'),
                                'orderable' => false,
                                'searchable' => false,
                                'width' => '70px',
                            ],
                        ];
                    @endphp

                    <x-data-table id="companyProposalsTemplateTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'. $type))" :is-checkbox="true"
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
