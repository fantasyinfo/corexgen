@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">{{ $title }}</h3>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">{{ $title }}</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModalWebToLead">Add
                        New</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped ">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Leads Capture</th>
                                <th>Group</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="webToLeadFormBody">
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModalWebToLead" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="createForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Add New +</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Groups <span class="text-danger">*</span></label>
                            <select class="form-select" name="group_id" id="group_id" required>
                                @foreach ($leadsGroups as $lg)
                                    <option value="{{ $lg->id }}" {{ old('group_id') == $lg->id ? 'selected' : '' }}>
                                        {{ $lg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Sources <span class="text-danger">*</span></label>
                            <select class="form-select" name="source_id" id="source_id" required>
                                @foreach ($leadsSources as $ls)
                                    <option value="{{ $ls->id }}" {{ old('source_id') == $ls->id ? 'selected' : '' }}>
                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_id" id="status_id" required>
                                @foreach ($leadsStatus as $ls)
                                    <option value="{{ $ls->id }}" {{ old('status_id') == $ls->id ? 'selected' : '' }}>
                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModalWebToLead" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="updateForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="updateId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateModalLabel">Update Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title_update" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title_update" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Groups <span class="text-danger">*</span></label>
                            <select class="form-select" name="group_id" id="group_id_update" required>
                                @foreach ($leadsGroups as $lg)
                                    <option value="{{ $lg->id }}"
                                        {{ old('group_id') == $lg->id ? 'selected' : '' }}>
                                        {{ $lg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Sources <span class="text-danger">*</span></label>
                            <select class="form-select" name="source_id" id="source_id_update" required>
                                @foreach ($leadsSources as $ls)
                                    <option value="{{ $ls->id }}"
                                        {{ old('source_id') == $ls->id ? 'selected' : '' }}>
                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status_id" id="status_id_update" required>
                                @foreach ($leadsStatus as $ls)
                                    <option value="{{ $ls->id }}"
                                        {{ old('status_id') == $ls->id ? 'selected' : '' }}>
                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Leads Modal -->
    <div class="modal fade" id="leadsModal" tabindex="-1" aria-labelledby="leadsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadsModalLabel">Captured Leads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="leadsContainer">
                        <!-- Leads list/table will be injected here -->
                    </div>
                    <!-- You can also add a link to a dedicated page if desired -->
                    <div class="mt-3">
                        <a href="/some-view-leads-page" class="btn btn-primary" id="viewLeadsPageLink">
                            Go to Full Leads Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {


                const route = "{{ route(getPanelRoutes($module . '.leadFormSettingFetch')) }}";
                // Function to load categories
                function loadForms() {
                    fetch(`${route}`)
                        .then(response => response.json())
                        .then(data => {
                            const tableBody = document.getElementById('webToLeadFormBody');
                            tableBody.innerHTML = data.map((form, index) => `
                            <tr>
                               
                                <td>${form.title}</td>
                                <td>${form.leads_count ?? 0}</td>
                 
                                <td>
                                    <span class="badge bg-${form.group.color}">
                                        ${form.group.name}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-${form.source.color}">
                                        ${form.source.name}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-${form.stage.color}">
                                        ${form.stage.name}
                                    </span>
                                </td>
                                <td>
                                    <button 
                                    title="Edit"
                                    data-toggle="tooltip"
                                    class="btn btn-sm btn-warning edit-btn" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateModalWebToLead"
                                        data-id="${form.id}"
                                        data-source_id="${form.source_id}"
                                        data-status_id="${form.status_id}"
                                        data-group_id="${form.group_id}"
                                        data-title="${form.title}">
                                        <i class="fas fa-pencil-alt me-2"></i>
                                    </button>
                                    <button 
                                    title="Delete"
                                    data-toggle="tooltip"
                                    class="btn btn-sm btn-danger delete-btn" 
                                    data-id="${form.id}"
                                    >
                                        <i class="fas fa-trash me-2"></i>
                                    </button>
                                    
                                    <button 
                                    title="Generate Form"
                                    data-toggle="tooltip"
                                    class="btn btn-sm btn-outline-primary generate-btn" 
                                    data-id="${form.id}"
                                    >
                                        <i class="fas fa-paper-plane me-2"></i>
                                    </button>

                                   
                                </td>
                            </tr>
                        `).join('');

                            // Reinitialize event listeners for edit and delete buttons
                            initializeEventListeners();
                        })
                        .catch(error => alert('Error:', error));
                }

                // Create category
                document.getElementById('createForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);


                    const createRoute = "{{ route(getPanelRoutes($module . '.leadFormSettingStore')) }}";

                    fetch(createRoute, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('createModalWebToLead'))
                                    .hide();
                                this.reset();
                                loadForms();
                                // Show success message
                                alert('Form created successfully');
                            }
                        })
                        .catch(error => alert('Error:', error));
                });

                // Update category
                document.getElementById('updateForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);


                    formData.append("_method", 'PUT');
                    const updateRoute = "{{ route(getPanelRoutes($module . '.leadFormSettingUpdate')) }}";

                    fetch(updateRoute, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                bootstrap.Modal.getInstance(document.getElementById('updateModalWebToLead'))
                                    .hide();
                                loadForms();
                                // Show success message
                                alert('Form updated successfully');
                            }
                        })
                        .catch(error => alert('Error:', error));
                });

                // Initialize event listeners for dynamic elements
                function initializeEventListeners() {
                    // Edit button handlers
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            console.log(this.dataset)
                            document.getElementById('updateId').value = this.dataset.id;
                            document.getElementById('source_id_update').value = this.dataset.source_id;
                            document.getElementById('group_id_update').value = this.dataset.group_id;
                            document.getElementById('status_id_update').value = this.dataset.status_id;
                            document.getElementById('title_update').value = this.dataset.title;
                        });
                    });

                    // Delete button handlers
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete this form?')) {
                                const formData = new FormData();
                                formData.append('_method', 'DELETE');


                                let deleteRoute =
                                    "{{ route(getPanelRoutes($module . '.leadFormSettingDestory'), ['id' => ':id']) }}";
                                deleteRoute = deleteRoute.replace(':id', this.dataset.id);


                                fetch(deleteRoute, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            loadForms();
                                            // Show success message
                                            alert('Form deleted successfully');
                                        }
                                    })
                                    .catch(error => alert('Error:', error));
                            }
                        });
                    });

                    // Delete button handlers
                    document.querySelectorAll('.generate-btn').forEach(button => {
                        button.addEventListener('click', function() {

                            let generateRoute =
                                "{{ route(getPanelRoutes($module . '.leadFormSettingGenerate'), ['id' => ':id']) }}";
                            generateRoute = generateRoute.replace(':id', this.dataset.id);

                            window.location.href = generateRoute
                        });
                    });


                }





                // Initial load
                loadForms();
            });
        </script>
    @endpush
@endsection
