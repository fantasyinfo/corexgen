@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header border-bottom pb-2">
                <div class="row ">
                    <div class="col-md-4">
                        <h5 class="card-title">{{ __('companies.Companies Management') }}</h5>
                    </div>
                    @include('layout.components.header-buttons')
                </div>
            </div>

            <div class="card-body">

                @include('dashboard.crm.companies.components.companies-filters')
                @include('layout.components.bulk-import-modal')


                @if (hasPermission('COMPANIES.READ_ALL') || hasPermission('COMPANIES.READ'))
                    <div class="table-responsive">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th >
                                        <input type="checkbox" id="select-all" /> 
                                    </th>
                                    <th> {{ __('companies.Name') }}</th>
                                    <th>{{ __('companies.Email') }}</th>
                                    <th>{{ __('companies.Plan') }}</th>
                                    <th>{{ __('companies.Billing Cycle') }}</th>
                                    <th>{{ __('companies.Subscription Start') }}</th>
                                    <th>{{ __('companies.Subscription End') }}</th>
                                    <th>{{ __('companies.Renew Date') }}</th>
                                    <th>{{ __('crud.Status') }}</th>
                                    <th>{{ __('crud.Created At') }}</th>

                                    <th class="text-end">{{ __('crud.Actions') }}</th>
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

@include('layout.components.bulk-import-js')

@push('scripts')
    <script type="text/javascript">
      
        $(document).ready(function() {

            const nameFilter = $('#nameFilter');
            const emailFilter = $('#emailFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');
            const plansFilter = $('#plansFilter');

        

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
                        d.start_date = startDateFilter.val();
                        d.end_date = endDateFilter.val();
                        d.plans = plansFilter.val();
                    },
                },
                columns: [
                    {
                        data: null, // Render checkbox for bulk actions
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                        },
                    },{
                        data: 'name',
                        name: 'name',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'email',
                        name: 'email',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'plan_name',
                        name: 'plan_name',
                        // searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'billing_cycle',
                        name: 'billing_cycle',
                        // searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'end_date',
                        name: 'end_date',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'next_billing_date',
                        name: 'next_billing_date',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'status',
                        name: 'status',
                        searchable: false, 
                        orderable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        searchable: true, 
                        orderable: true
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            // Clear Filter Button
            $('#clearFilter').on('click', function() {
                // Reset all filter input fields
                nameFilter.val('');
                emailFilter.val('');
                statusFilter.val('');
                startDateFilter.val('');
                endDateFilter.val('');
                plansFilter.val('');
                // Reset the DataTable's search and reload
                dbTableAjax.ajax.reload();
            });


            $('#filterBtn').click(function() {
                dbTableAjax.ajax.reload();
                const filterSidebar = document.getElementById("filterSidebar");
                filterSidebar.classList.remove('show');
            });

                 // "Select All" functionality
                 $('#select-all').on('click', function() {
                let isChecked = $(this).is(':checked');
                $('.bulk-select').prop('checked', isChecked);
            });

            $('#bulk-delete-btn').on('click', function() {
                let selectedIds = [];
                $('.bulk-select:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length > 0) {
                    if (confirm('Are you sure you want to delete the selected companies?')) {
                        $.ajax({
                            url: "{{ route(getPanelRoutes($module . '.bulkDelete')) }}",
                            method: "POST",
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}' // CSRF token for security
                            },
                            success: function(response) {
                                alert(response.message);
                                dbTableAjax.ajax.reload(); // Reload DataTable
                            },
                            error: function(error) {
                                alert('An error occurred while deleting companies.');
                            }
                        });
                    }
                } else {
                    alert('No companies selected for deletion.');
                }
            });
        });
    </script>
@endpush
