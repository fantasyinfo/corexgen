@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-2">
                <h5 class="card-title">{{ __('users.Users Management') }}</h5>
                <div class="card-header-action">
                    @if (hasPermission('ROLE.CREATE'))
                        <a data-toggle="tooltip" data-placement="top" title="Create New" href="{{ route('crm.users.create') }}"
                            class="btn btn-md btn-primary me-2">
                            <i class="fas fa-plus"></i> <span>{{ __('users.Create User') }}</span>
                        </a>
                    @endif
                    @if (hasPermission('ROLE.EXPORT'))
                        <a data-toggle="tooltip" data-placement="top" title="Export Data"
                            href="{{ route('crm.users.export', request()->all()) }}"
                            class="btn btn-md btn-outline-secondary">
                            <i class="fas fa-download"></i> <span>{{ __('crud.Export') }}</span>
                        </a>
                    @endif
                    @if (hasPermission('ROLE.IMPORT'))
                        <button data-toggle="tooltip" data-placement="top" title="Import Data" data-bs-toggle="modal"
                            data-bs-target="#bulkImportModal" class="btn btn-md btn-outline-info">
                            <i class="fas fa-upload"></i><span> {{ __('crud.Import') }}</span>
                        </button>
                    @endif
                    @if (hasPermission('ROLE.FILTER'))
                        <button data-toggle="tooltip" data-placement="top" title="Filter Data" onclick="openFilters()"
                            class="btn btn-md btn-outline-warning">
                            <i class="fas fa-filter"></i>
                            <span>{{ __('crud.Filter') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body">


                @if (hasPermission('ROLE.FILTER'))
                    <div id="filter-section">

                        <div class="card-title">
                            {{ __('crud.Filter') }}

                        </div>
                        <!-- Advanced Filter Form -->

                        <div class="row g-3">
                            <!-- Search Input -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nameFilter" class="mb-2 font-12">{{ __('users.Name') }}</label>
                                    <input type="text" id="nameFilter" name="name" class="form-control"
                                        placeholder="{{ __('users.Name') }}" value="{{ request('name') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="emailFilter" class="mb-2 font-12">{{ __('users.Email') }}</label>
                                    <input type="text" id="emailFilter" name="email" class="form-control"
                                        placeholder="{{ __('users.Email') }}" value="{{ request('email') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="roleFilter" class="mb-2 font-12">{{ __('users.Role') }}</label>
                                    <select 
                                    id="roleFilter"
                                    class="form-control select2-hidden-accessible @error('role_id') is-invalid @enderror" name="role_id" id="role_id">
                                        @if($roles && $roles->isNotEmpty())
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option disabled>No roles available</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            

                            <!-- Status Dropdown -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="statusFilter" class="mb-2 font-12">{{ __('users.All Statuses') }}</label>
                                    <select name="status" class="form-select" id="statusFilter">
                                        <option value="">{{ __('users.All Statuses') }}</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                            {{ __('Active') }}
                                        </option>
                                        <option value="deactive" {{ request('status') == 'deactive' ? 'selected' : '' }}>
                                            {{ __('Inactive') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="button" id="filterBtn" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>{{ __('Search') }}
                                    </button>
                                    <button type="button" id="clearFilter" class="btn btn-light">
                                        <i class="fas fa-trash-alt me-1"></i>{{ __('Clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                @endif


                @if (hasPermission('ROLE.READ_ALL') || hasPermission('ROLE.READ'))
                    <div class="table-responsive card">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th> {{ __('users.Name') }}</th>
                                    <th>{{ __('users.Email') }}</th>
                                    <th>{{ __('users.Role') }}</th>
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
        <div class="modal-dialog modal-lg" users="document">
            <div class="modal-content">
                <form id="bulkImportForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkImportModalLabel">Bulk Import Users</h5>
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
        // Get the drop zone element
        const dropZone = document.querySelector('.drop-zone');
        const fileInput = document.querySelector('#csvFile');

        // Click handler (you already have this)
        dropZone.addEventListener('click', function() {
            fileInput.click();
        });

        // File input change handler (you already have this)
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                this.nextElementSibling.textContent = file.name;
            }
        });

        // Add drag and drop event listeners
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '#f8f9fa';
            dropZone.style.borderColor = '#0d6efd';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '#ddd';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.backgroundColor = '';
            dropZone.style.borderColor = '#ddd';

            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                // Trigger the change event manually
                const event = new Event('change', {
                    bubbles: true
                });
                fileInput.dispatchEvent(event);

                // Update the text
                fileInput.nextElementSibling.textContent = files[0].name;
            }
        });

        // Form submit handler (you already have this)
        document.querySelector('#bulkImportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const response = await fetch('{{ route('crm.users.import') }}', {
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
            const emailFilter = $('#emailFilter');
            const roleFilter = $('#roleFilter');
            const statusFilter = $('#statusFilter');
            const startDateFilter = $('#startDateFilter');
            const endDateFilter = $('#endDateFilter');

            const dbTableAjax = $("#userTable").DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('crm.users.index') }}",
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
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role_name',
                        name: 'role_name'
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
            });
        });
    </script>
@endpush
