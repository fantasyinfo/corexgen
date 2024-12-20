@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded ">
                @include('dashboard.role.components.role-filters')

                @include('layout.components.bulk-import-modal')




                @if (hasPermission('ROLE.READ_ALL') || hasPermission('ROLE.READ'))
                    <div class="table-responsive table-bg ">

                        <table id="roleTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="select-all" />
                                    </th>
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
                stateSave: true,
                order: [],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
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
                        data: null, // Render checkbox for bulk actions
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `<input type="checkbox" class="bulk-select" data-id="${row.id}" />`;
                        },
                    },
                    {
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
                const filterSidebar = document.getElementById("filterSidebar");
                filterSidebar.classList.remove('show');
            });

            // "Select All" functionality
            $('#select-all').on('click', function() {
                let isChecked = $(this).is(':checked');
                $('.bulk-select').prop('checked', isChecked);
            });

            // bulk delete btn
            $('#bulk-delete-btn').on('click', function() {
                let selectedIds = [];
                $('.bulk-select:checked').each(function() {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length > 0) {
                    // Show the custom confirmation modal
                    $('#bulkDeleteModal').modal('show');

                    // Attach the event to the confirm button in the modal
                    $('#confirmDeleteBtn').off('click').on('click', function() {
                        $.ajax({
                            url: "{{ route(getPanelRoutes($module . '.bulkDelete')) }}",
                            method: "POST",
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}' // CSRF token for security
                            },
                            success: function(response) {
                                // Hide the confirmation modal
                                $('#bulkDeleteModal').modal('hide');

                                // Show the success modal with the response message
                                $('#successModal .modal-body').text(response
                                    .message);
                                $('#successModal').modal('show');

                                // Reload the DataTable
                                dbTableAjax.ajax.reload();
                            },
                            error: function(error) {
                                // Handle errors (optional)
                                $('#bulkDeleteModal').modal('hide');
                                alert(
                                    'An error occurred while deleting items.');
                            }
                        });
                    });
                } else {
                    // No items selected
                    $('#alertModal .modal-body').text('No items selected for deletion.');
                    $('#alertModal').modal('show');
                }
            });

        });
    </script>
@endpush
