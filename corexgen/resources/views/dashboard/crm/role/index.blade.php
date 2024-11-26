@extends('layout.app')

@section('content')
    <div class="container-fluid ">
        <div class="card shadow-sm rounded p-3">
            <div class="card-header  border-bottom pb-2">
                <div class="row ">
                    <div class="col-md-4">
                        <h5 class="card-title">{{ __('crm_role.Roles Management') }}</h5>
                    </div>
                    @include('dashboard.crm.role.components.role-actions')
                </div>


            </div>

            <div class="card-body">
                @include('dashboard.crm.role.components.role-filters')


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

    <!-- Modal -->
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="bulkImportForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkImportModalLabel">Bulk Import Roles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Upload CSV File</label>
                            <div class="drop-zone">
                                <input type="file" name="file" id="csvFile" class="form-control" accept=".csv"
                                    style="display: none;" />
                                <p>Drag & Drop your file here or click to browse</p>
                            </div>
                            <small class="form-text text-muted">Only CSV files are allowed. Max size: 2MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection



@push('scripts')
    <script type="text/javascript">
        // Form submit handler (you already have this)
        document.querySelector('#bulkImportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const response = await fetch('{{ route(getPanelRoutes('role.import')) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || 'Import failed. Please check the file format.');
            }
        });

        $(document).ready(function() {

            const nameFilter = $('#nameFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#roleTable").DataTable({
                processing: true,
                serverSide: true,
                "language": {
                    "lengthMenu": "_MENU_ per page",
                },
                ajax: {
                    url: "{{ route(getPanelRoutes('role.index')) }}",
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


            $('#filterBtn').click(function() {
                dbTableAjax.ajax.reload();
            });

            $('#deleteModal').on('show.bs.modal', function(event) {
                console.log('first')
                var button = $(event.relatedTarget); // The button that triggered the modal
                var roleId = button.data('id'); // Get the role ID
                var route = button.data('route'); // Get the delete route

                // Set the form action to the appropriate route
                var form = $('#deleteForm');
                form.attr('action', route);
            });
        });
    </script>
@endpush
