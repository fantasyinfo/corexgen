@push('scripts')
    <script>
        const _page = "{{ $page }}";

        let params = {
            relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients'] }}',
            type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'] }}',
        };

        switch (_page) {
            case 'clientsCategory':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'] }}',
                }
                break;
            case 'leadsGroups':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'] }}',
                }
                break;
            case 'leadsStatus':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'] }}',
                }
                break;
            case 'leadsSources':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'] }}',
                }
                break;
            case 'productsCategories':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['products_categories'] }}',
                }
                break;
            case 'productsTaxes':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['products_taxs'] }}',
                }
                break;
            case 'tasksStatus':
                params = {
                    relation_type: '{{ CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks'] }}',
                    type: '{{ CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'] }}',
                }
                break;

            default:
                break;
        }
    </script>
@endpush


@extends('dashboard.settings.settings-layout')
@section('settings_content')
    <div class="container-fluid">
        <h3 class="mb-4">{{ $title }}</h3>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">{{ $title }}</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">Add New</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Color</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
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
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <select name="color" class="form-control" required>
                                <option value="success">Success</option>
                                <option value="danger">Danger</option>
                                <option value="warning">Warning</option>
                                <option value="dark">Dark</option>
                                <option value="light">Light</option>
                                <option value="info">Info</option>
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
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
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
                            <label for="updateName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="updateName" name="name" required>
                        </div>
                        <div class="mb-3">

                            <div class="mb-3">
                                <label for="color" class="form-label">Color</label>
                                <select name="color" id="updateColor" class="form-control" required>
                                    <option value="success">Success</option>
                                    <option value="danger">Danger</option>
                                    <option value="warning">Warning</option>
                                    <option value="dark">Dark</option>
                                    <option value="light">Light</option>
                                    <option value="info">Info</option>
                                </select>
                            </div>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
               

                const route = "{{ route(getPanelRoutes($module . '.index')) }}";
                // Function to load categories
                function loadCategories() {
                    fetch(`${route}?${new URLSearchParams(params)}`)
                        .then(response => response.json())
                        .then(data => {
                            const tableBody = document.getElementById('categoriesTableBody');
                            tableBody.innerHTML = data.map((category, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${category.name}</td>
                                <td>
                                    <span class="badge bg-${category.color}">
                                        ${category.color.toUpperCase()}
                                    </span>
                                </td>
                                <td>
                                    <button 
                                    title="Edit"
                                    data-toggle="tooltip"
                                    class="btn btn-sm btn-warning edit-btn" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateModal"
                                        data-id="${category.id}"
                                        data-name="${category.name}"
                                        data-color="${category.color}">
                                        <i class="fas fa-pencil-alt me-2"></i>
                                    </button>
                                    <button 
                                    title="Delete"
                                    data-toggle="tooltip"
                                    class="btn btn-sm btn-danger delete-btn" 
                                        data-id="${category.id}">
                                        <i class="fas fa-trash me-2"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('');

                            // Reinitialize event listeners for edit and delete buttons
                            initializeEventListeners();
                        })
                        .catch(error => console.error('Error:', error));
                }

                // Create category
                document.getElementById('createForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    Object.entries(params).forEach(([key, value]) => {
                        formData.append(key, value);
                    });

                    const createRoute = "{{ route(getPanelRoutes($module . '.store')) }}";

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
                                bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
                                this.reset();
                                loadCategories();
                                // Show success message
                                alert('Category created successfully');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });

                // Update category
                document.getElementById('updateForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    Object.entries(params).forEach(([key, value]) => {
                        formData.append(key, value);
                    });

                    formData.append("_method", 'PUT');
                    const updateRoute = "{{ route(getPanelRoutes($module . '.update')) }}";

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
                                bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
                                loadCategories();
                                // Show success message
                                alert('Category updated successfully');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });

                // Initialize event listeners for dynamic elements
                function initializeEventListeners() {
                    // Edit button handlers
                    document.querySelectorAll('.edit-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            document.getElementById('updateId').value = this.dataset.id;
                            document.getElementById('updateName').value = this.dataset.name;
                            document.getElementById('updateColor').value = this.dataset.color;
                        });
                    });

                    // Delete button handlers
                    document.querySelectorAll('.delete-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            if (confirm('Are you sure you want to delete this category?')) {
                                const formData = new FormData();
                                formData.append('_method', 'DELETE');
                                Object.entries(params).forEach(([key, value]) => {
                                    formData.append(key, value);
                                });

                                let deleteRoute =
                                    "{{ route(getPanelRoutes($module . '.destroy'), ['id' => ':id']) }}";
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
                                            loadCategories();
                                            // Show success message
                                            alert('Category deleted successfully');
                                        }
                                    })
                                    .catch(error => console.error('Error:', error));
                            }
                        });
                    });
                }

                // Initial load
                loadCategories();
            });
        </script>
    @endpush
@endsection

