@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="card shadow-sm rounded p-3">
            <div class="card-header  border-bottom pb-2">
                <div class="row ">
                    <div class="col-md-4">
                        <h5 class="card-title">{{ __('crm_role.Roles Management') }}</h5>
                    </div>
                    @include('layout.components.header-buttons')
                </div>


            </div>

            <div class="card-body">
                @include('dashboard.crm.role.components.role-filters')
                @include('layout.components.bulk-import-modal')


                @if (hasPermission('ROLE.READ_ALL') || hasPermission('ROLE.READ'))
                    <div class="table-responsive ">

                        <table id="roleTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th> {{ __('crm_role.Role Name') }}</th>
                                    <th>{{ __('crm_role.Description') }}</th>
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
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#roleTable").DataTable({
                processing: true,
                serverSide: true,
                language: {
                    "lengthMenu": "_MENU_ per page",
                },
                ajax: {
                    url: "{{ route(getPanelRoutes($module . '.index')) }}",
                    data: function(d) {
                        // Add filters if required
                        d.name = nameFilter.val();
                        d.status = $('#statusFilter').val();
                        d.start_date = $('#startDateFilter').val();
                        d.end_date = $('#endDateFilter').val();
                    },
                },
                columns: [{
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'role_desc',
                        name: 'role_desc'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
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
                statusFilter.val('');
                startDateFilter.val('');
                endDateFilter.val('');
                // Reset the DataTable's search and reload
                dbTableAjax.ajax.reload();
            });

            $("#filterBtn").click(function() {
                dbTableAjax.ajax.reload();
            });

        });
    </script>
@endpush
