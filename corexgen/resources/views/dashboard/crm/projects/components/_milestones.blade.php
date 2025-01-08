<!-- Add this button above your table -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Milestones Lists</h5>
    <button type="button" class="btn btn-primary" id="createMilestoneBtn">
        <i class="fas fa-plus"></i> Create Milestone
    </button>
</div>

<!-- Modal for Create/Edit -->
<div class="modal fade" id="milestoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="milestoneForm">
                    @csrf
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="project_id" name="project_id" value="{{ $project?->id }}">
                    <div class="mb-3">
                        <x-form-components.input-label for="name" class="custom-class" required>
                            Name
                        </x-form-components.input-label>
                        <x-form-components.input-group type="text" name="name" id="name"
                            placeholder="{{ __('Logo Design') }}" required class="custom-class" />
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <x-form-components.input-label for="color" class="custom-class" required>
                            Color
                        </x-form-components.input-label>
                        <select name="color" id="color" class="form-select">
                            <option value="success">Success</option>
                            <option value="danger">Danger</option>
                            <option value="warning">Warning</option>
                            <option value="dark">Dark</option>
                            <option value="light">Light</option>
                            <option value="info">Info</option>
                        </select>
                        <div class="invalid-feedback" id="colorError"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveButton">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModalMilestone" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Milestone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this milestone?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteMilestone">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Update the table's action column -->

<div class="timeline-wrapper">
    @if ($milestones && $milestones->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Color</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($milestones as $ml)
                        <tr>
                            <td>{{ $ml->name }}</td>
                            <td><span class="badge  bg-{{ $ml->color }}">{{ucwords($ml->color) }}</span></td>
                            <td>{{ formatDateTime($ml?->created_at) }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-milestone" data-id="{{ $ml->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-milestone" data-id="{{ $ml->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h6>No Milestones Yet</h6>
            <p class="text-muted">Milestones will appear here, if any.</p>
        </div>
    @endif
</div>



@push('scripts')
    <script>
        // Add this to your JavaScript file
        $(document).ready(function() {
            // Bootstrap Modal instance
            const milestoneModal = new bootstrap.Modal(document.getElementById('milestoneModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModalMilestone'));
            let deleteId = null;

            // Create button click handler
            $('#createMilestoneBtn').click(function() {
                $('#modalTitle').text('Create Milestone');
                $('#milestoneForm')[0].reset();
                $('#id').val('');
                clearErrors();
                milestoneModal.show();
            });



            let milestoneCreateRoute = "{{ route(getPanelRoutes($module . '.storeMilestones')) }}";



            // Edit button click handler
            $(document).on('click', '.edit-milestone', function() {
                const id = $(this).data('id');
                clearErrors();

                let baseUrl = "{{ route(getPanelRoutes($module . '.editMilestones'), ['id' => ':id']) }}";
                let editURL = baseUrl.replace(':id', id);
                // Fetch milestone data
                $.ajax({
                    url: `${editURL}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modalTitle').text('Edit Milestone');
                        $('#id').val(response.id);
                        $('#name').val(response.name);
                        $('#color').val(response.color);
                        milestoneModal.show();
                    },
                    error: function(xhr) {
                        showToast('Error fetching milestone data', 'error');
                    }
                });
            });

            // Delete button click handler
            $(document).on('click', '.delete-milestone', function() {
                deleteId = $(this).data('id');
                deleteModal.show();
            });

            // Confirm delete handler
            $('#confirmDeleteMilestone').click(function() {
                if (deleteId) {

                    let baseUrlD = "{{ route(getPanelRoutes($module . '.destroyMilestones'), ['id' => ':id']) }}";
                    let deleteURL = baseUrlD.replace(':id', deleteId);

                    $.ajax({
                        url: `${deleteURL}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            deleteModal.hide();
                            refreshTable();
                            showToast('Milestone deleted successfully', 'success');
                        },
                        error: function(xhr) {
                            showToast('Error deleting milestone', 'error');
                        }
                    });
                }
            });

            // Save button click handler
            $('#saveButton').click(function() {
                const id = $('#id').val();
                const isEdit = !!id;
                const formData = new FormData($('#milestoneForm')[0]);

                let storeURL = "{{ route(getPanelRoutes($module . '.storeMilestones')) }}";
                let updateURL = "{{ route(getPanelRoutes($module . '.updateMilestones')) }}";

                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                if (isEdit) {
                    formData.append('_method', 'PUT'); // Add _method for PUT requests
                }

                $.ajax({
                    url: isEdit ? `${updateURL}` : `${storeURL}`,
                    method: 'POST', // Always use POST for both
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        milestoneModal.hide();
                        refreshTable();
                        showToast(`Milestone ${isEdit ? 'updated' : 'created'} successfully`,
                            'success');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            showErrors(errors);
                        } else {
                            showToast('An error occurred', 'error');
                        }
                    }
                });
            });


            // Helper functions
            function clearErrors() {
                $('.invalid-feedback').empty();
                $('.is-invalid').removeClass('is-invalid');
            }

            function showErrors(errors) {
                clearErrors();
                for (const field in errors) {
                    $(`#${field}`).addClass('is-invalid');
                    $(`#${field}Error`).text(errors[field][0]);
                }
            }

            function refreshTable() {
                location.reload();
            }

            function showToast(message, type = 'success') {
                // Implement your preferred toast notification here
                // Example using Bootstrap Toast:
                const toast = `
            <div class="toast align-items-center text-white bg-${type}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
                $('.toast-container').append(toast);
                const toastElement = new bootstrap.Toast(document.querySelector('.toast:last-child'));
                toastElement.show();
            }
        });
    </script>
@endpush
