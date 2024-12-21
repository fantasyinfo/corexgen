@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="">
            {{-- @include('layout.components.header-buttons') --}}

            <div class="shadow-sm rounded ">
                {{-- @include('dashboard.role.components.role-filters') --}}


                @if (hasPermission('PAYMENTGATEWAYS.READ_ALL') || hasPermission('PAYMENTGATEWAYS.READ'))
                    @php

                        $columns = [
                            [
                                'data' => 'logo',
                                'name' => 'logo',
                                'label' => __('paymentgateway.Logo'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'official_website',
                                'name' => 'official_website',
                                'label' => __('paymentgateway.Website'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'name',
                                'name' => 'name',
                                'label' => __('paymentgateway.Name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'type',
                                'name' => 'type',
                                'label' => __('paymentgateway.Type'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'mode',
                                'name' => 'mode',
                                'label' => __('paymentgateway.Mode'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'status',
                                'name' => 'status',
                                'label' => __('crud.Status'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'actions',
                                'name' => 'actions',
                                'label' => __('crud.Action'),
                                'orderable' => false,
                                'searchable' => false,
                                'width' => '100px',
                            ],
                        ];
                    @endphp
                    <x-data-table id="paymentGatewaysTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" />
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
