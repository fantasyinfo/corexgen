@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                {{-- @include('dashboard.crm.users.components.users-filters') --}}
                {{-- @include('layout.components.bulk-import-modal') --}}


                @if (hasPermission('SUBSCRIPTIONS.READ_ALL') || hasPermission('SUBSCRIPTIONS.READ'))
                    <div class="table-responsive table-bg">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                
                                    <th> {{ __('subscription.Company name') }}</th>
                                    <th>{{ __('subscription.Plan Name') }}</th>
                                    <th>{{ __('subscription.Start Date') }}</th>
                                    <th>{{ __('subscription.End Date') }}</th>
                                    <th>{{ __('subscription.Next Billing Date') }}</th>
                                    <th>{{ __('subscription.Billing Cycle') }}</th>
                                    <th>{{ __('subscription.Upgrade Date') }}</th>
                                    <th>{{ __('subscription.Currency') }}</th>
                                    <th>{{ __('subscription.Amount') }}</th>
                                    <th>{{ __('subscription.Gateway') }}</th>
                                    <th>{{ __('subscription.Type') }}</th>
                                    {{-- <th>{{ __('subscription.Transaction Date') }}</th> --}}
                                    {{-- <th>{{ __('subscription.Plan Start Date') }}</th> --}}
                                    {{-- <th>{{ __('crud.Status') }}</th> --}}
                                    <th>{{ __('crud.Created At') }}</th>

                                    {{-- <th class="text-end">{{ __('crud.Actions') }}</th> --}}
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>


                    </div>
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

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            const nameFilter = $('#nameFilter');
            const emailFilter = $('#emailFilter');
            const roleFilter = $('#roleFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#userTable").DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
         
                language: {
                    "lengthMenu": "_MENU_ per page",
                },
                ajax: {
                    url: "{{ route(getPanelRoutes($module . '.index')) }}",
                    data: function(d) {
                        // Add filters if required
                        d.name = nameFilter.val();
                        d.email = emailFilter.val();
                        d.status = statusFilter.val();
                        d.role_id = roleFilter.val();
                        d.start_date = startDateFilter.val();
                        d.end_date = endDateFilter.val();
                    },
                },
               
                columns: [
                    {
                        data: 'name',
                        name: 'name',
                        searchable: true,
                        orderable: true,
                        width: '200px',
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'next_billing_date',
                        name: 'next_billing_date',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'billing_cycle',
                        name: 'billing_cycle',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'upgrade_date',
                        name: 'upgrade_date',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'currency',
                        name: 'currency',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'payment_gateway',
                        name: 'payment_gateway',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'payment_type',
                        name: 'payment_type',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                 
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                   
                ],
            });

            // Clear Filter Button
            $('#clearFilter').on('click', function() {
                // Reset all filter input fields
                nameFilter.val('');
                emailFilter.val('');
                roleFilter.val('');
                statusFilter.val('');
                startDateFilter.val('');
                endDateFilter.val('');
                // Reset the DataTable's search and reload
                dbTableAjax.ajax.reload();
            });


            $('#filterBtn').click(function() {
                dbTableAjax.ajax.reload();
                const filterSidebar = document.getElementById("filterSidebar");
                filterSidebar.classList.remove('show');
            });

        


        });

     
    </script>
@endpush
