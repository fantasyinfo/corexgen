@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                {{-- @include('dashboard.crm.users.components.users-filters') --}}
                {{-- @include('layout.components.bulk-import-modal') --}}


                @if (hasPermission('PAYMENTSTRANSACTIONS.READ_ALL') || hasPermission('PAYMENTSTRANSACTIONS.READ'))
                    @php

                        $columns = [
                            [
                                'data' => 'name',
                                'name' => 'company.name',
                                'label' => __('planspaymenttransaction.Company name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'plans.name',
                                'name' => 'plans.name',
                                'label' => __('planspaymenttransaction.Plan Name'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '200px',
                            ],
                            [
                                'data' => 'currency',
                                'name' => 'currency',
                                'label' => __('planspaymenttransaction.Currency'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'amount',
                                'name' => 'amount',
                                'label' => __('planspaymenttransaction.Amount'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'payment_gateway',
                                'name' => 'payment_gateway',
                                'label' => __('planspaymenttransaction.Gateway'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'payment_type',
                                'name' => 'payment_type',
                                'label' => __('planspaymenttransaction.Type'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            [
                                'data' => 'transaction_date',
                                'name' => 'transaction_date',
                                'label' => __('planspaymenttransaction.Transaction Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            [
                                'data' => 'subscription.start_date',
                                'name' => 'subscription.start_date',
                                'label' => __('planspaymenttransaction.Plan Start Date'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '150px',
                            ],
                            // [
                            //     'data'=> 'status',
                            //     'name'=> 'status',
                            //     'label'=> __('crud.Status'),
                            //     'searchable'=> false,
                            //     'orderable'=> true,
                            //     'width'=> '100px',
                            // ],
                            [
                                'data' => 'created_at',
                                'name' => 'created_at',
                                'label' => __('crud.Created At'),
                                'searchable' => true,
                                'orderable' => true,
                                'width' => '100px',
                            ],
                            // [
                            //     'data'=> 'actions',
                            //     'name'=> 'actions',
                            //     'label'=> 'label' => __('crud.Action'),
                            //     'orderable'=> false,
                            //     'searchable'=> false,
                            //     'width'=> '100px',
                            // ],
                        ];
                    @endphp
                    <x-data-table id="plansPaymentTransactionTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" />
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
