@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                {{-- @include('dashboard.crm.users.components.users-filters') --}}
                {{-- @include('layout.components.bulk-import-modal') --}}


                @if (hasPermission('SUBSCRIPTIONS.READ_ALL') || hasPermission('SUBSCRIPTIONS.READ'))

                @php

                $columns = [
                    [
                        'data' => 'name',
                        'name' => 'company.name',
                        'label' => __('subscription.Company name'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '200px',
                    ],
                    [
                        'data' => 'plans.name',
                        'name' => 'plans.name',
                        'label' => __('subscription.Plan Name'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '200px',
                    ],
                    [
                        'data' => 'start_date',
                        'name' => 'start_date',
                        'label' => __('subscription.Start Date'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'end_date',
                        'name' => 'end_date',
                        'label' => __('subscription.End Date'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'next_billing_date',
                        'name' => 'next_billing_date',
                        'label' => __('subscription.Next Billing Date'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '150px',
                    ],
                    [
                        'data' => 'billing_cycle',
                        'name' => 'billing_cycle',
                        'label' => __('subscription.Billing Cycle'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'upgrade_date',
                        'name' => 'upgrade_date',
                        'label' => __('subscription.Upgrade Date'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'payment_transaction.amount',
                        'name' => 'payment_transaction.amount',
                        'label' => __('subscription.Amount'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'payment_transaction.payment_gateway',
                        'name' => 'payment_transaction.payment_gateway',
                        'label' => __('subscription.Gateway'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
                    ],
                    [
                        'data' => 'payment_transaction.payment_type',
                        'name' => 'payment_transaction.payment_type',
                        'label' => __('subscription.Type'),
                        'searchable' => true,
                        'orderable' => true,
                        'width' => '100px',
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
            <x-data-table id="subscriptionsTable" :columns="$columns" :ajax-url="route(getPanelRoutes($module . '.index'))" />


                
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

{{-- @include('layout.components.bulk-import-js') --}}

