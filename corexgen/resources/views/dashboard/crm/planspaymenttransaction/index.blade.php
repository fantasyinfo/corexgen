@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                {{-- @include('dashboard.crm.users.components.users-filters') --}}
                {{-- @include('layout.components.bulk-import-modal') --}}


                @if (hasPermission('PAYMENTSTRANSACTIONS.READ_ALL') || hasPermission('PAYMENTSTRANSACTIONS.READ'))
                    <div class="table-responsive table-bg">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                
                                    <th> {{ __('planspaymenttransaction.Company name') }}</th>
                                    <th>{{ __('planspaymenttransaction.Plan Name') }}</th>
                                    <th>{{ __('planspaymenttransaction.Currency') }}</th>
                                    <th>{{ __('planspaymenttransaction.Amount') }}</th>
                                    <th>{{ __('planspaymenttransaction.Gateway') }}</th>
                                    <th>{{ __('planspaymenttransaction.Type') }}</th>
                                    <th>{{ __('planspaymenttransaction.Transaction Date') }}</th>
                                    <th>{{ __('planspaymenttransaction.Plan Start Date') }}</th>
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
                        data: 'transaction_date',
                        name: 'transaction_date',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        searchable: false,
                        orderable: true,
                        width: '100px',
                    },
                    // {
                    //     data: 'status',
                    //     name: 'status',
                    //     searchable: false,
                    //     orderable: true,
                    //     width: '100px',
                    // },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: true,
                        orderable: true,
                        width: '100px',
                    },
                    // {
                    //     data: 'actions',
                    //     name: 'actions',
                    //     orderable: false,
                    //     searchable: false,
                    //     width: '100px',
                    // },
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
