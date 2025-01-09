<!-- Add this button above your table -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Timesheets Lists</h5>
    <button type="button" class="btn btn-primary" id="createTimesheetBtn">
        <i class="fas fa-plus"></i> Create Timesheet
    </button>
</div>

@php
    // prePrintR($taskUsers);
@endphp

<!-- Modal for Create/Edit -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Timesheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="TimesheetForm">
                    @csrf
                    <input type="hidden" id="timesheet_id" name="id">
                    <input type="hidden" id="task_id" name="task_id" value="{{$task?->id}}">
                    <div class="mb-3">
                        <x-form-components.input-label for="name" class="custom-class" required>
                            Start Date & Time
                        </x-form-components.input-label>
                        <x-form-components.input-group type="datetime-local" name="start_date" id="start_date_t"
                            placeholder="{{ __('Select Date & Time') }}" required class="custom-class" />

                    </div>
                    <div class="mb-3">
                        <x-form-components.input-label for="name" class="custom-class" required>
                            End Date & Time
                        </x-form-components.input-label>
                        <x-form-components.input-group type="datetime-local" name="end_date" id="end_date_t"
                            placeholder="{{ __('Select Date & Time') }}" required class="custom-class" />

                    </div>
                   
                    <div class="mb-3">
                        <x-form-components.input-label for="user_id" class="custom-class" required>
                            User
                        </x-form-components.input-label>
                        <select name="user_id" id="user_id" class="form-select ">
                            <option>Select User</option>
                            @foreach ($taskUsers as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>

                    </div>

                    <div class="mb-3">
                        <x-form-components.input-label for="name" class="custom-class">
                            Notes
                        </x-form-components.input-label>
                        <x-form-components.textarea-group type="textarea" name="notes" id="notes"
                            placeholder="{{ __('Enter notes....') }}" class="custom-class" />

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveButtonTimeSheet">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModalTimesheet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Timesheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this Timesheet?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTimesheet">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Update the table's action column -->

<div class="timeline-wrapper">
    @if ($timesheets && $timesheets->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Task</th>
                        <th>User</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($timesheets as $tm)
                        <tr>
                            <td>{{ formatDateTime($tm?->start_date) }}</td>
                            <td>{{ formatDateTime($tm?->end_date) }}</td>
                            <td>{{ convertMinutesToHoursAndMinutes($tm?->duration) }}</td>
                            <td>
                                <a class="dt-link" href="{{ route(getPanelRoutes('tasks.view'), $tm?->task?->id) }}"
                                    target="_blank">
                                    {{ $tm?->task?->title }}
                                </a>
                            </td>

                            <td>
                                <a href="{{ route(getPanelRoutes('users.view'), ['id' => $tm?->user?->id]) }}">
                                    <x-form-components.profile-avatar :hw="40" :url="asset(
                                        'storage/' . ($tm?->user?->profile_photo_path ?? 'avatars/default.webp'),
                                    )"
                                        :title="$tm?->user?->name" />
                                </a>
                            </td>
                            <td>{{ formatDateTime($tm?->created_at) }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-Timesheet" data-id="{{ $tm->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-Timesheet" data-id="{{ $tm->id }}">
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
                <i class="fas fa-clock"></i>
            </div>
            <h6>No Timesheets Yet</h6>
            <p class="text-muted">Timesheets will appear here, if any.</p>
        </div>
    @endif
</div>



@push('scripts')
    <script>
        // Add this to your JavaScript file
        $(document).ready(function() {


            // Bootstrap Modal instance
            const timesheetModal = new bootstrap.Modal(document.getElementById('timesheetModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModalTimesheet'));
            let deleteId = null;

            // Create button click handler
            $('#createTimesheetBtn').click(function() {
                $('#modalTitle').text('Create Timesheet');
                $('#TimesheetForm')[0].reset();
                $('#timesheet_id').val('');
                clearErrors();
                timesheetModal.show();
            });



            let TimesheetCreateRoute = "{{ route(getPanelRoutes('projects' . '.storeTimesheets')) }}";



            // Edit button click handler
            $(document).on('click', '.edit-Timesheet', function() {
                const id = $(this).data('id');
                clearErrors();

                let baseUrl = "{{ route(getPanelRoutes('projects' . '.editTimesheets'), ['id' => ':id']) }}";
                let editURL = baseUrl.replace(':id', id);
                // Fetch Timesheet data
                $.ajax({
                    url: `${editURL}`,
                    method: 'GET',
                    success: function(response) {
                        $('#modalTitle').text('Edit Timesheet');
                        $('#timesheet_id').val(response.id);
                        $('#start_date_t').val(new Date(response.start_date).toISOString().slice(
                            0, 16));
                        $('#end_date_t').val(new Date(response.end_date).toISOString().slice(0,
                            16));
                      
                        $('#notes').val(response.notes);
                        timesheetModal.show();
                    },
                    error: function(xhr) {
                        showToast('Error fetching Timesheet data', 'error');
                    }
                });
            });

            // Delete button click handler
            $(document).on('click', '.delete-Timesheet', function() {
                deleteId = $(this).data('id');
                deleteModal.show();
            });

            // Confirm delete handler
            $('#confirmDeleteTimesheet').click(function() {
                if (deleteId) {

                    let baseUrlD =
                        "{{ route(getPanelRoutes('projects' . '.destroyTimesheets'), ['id' => ':id']) }}";
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
                            showToast('Timesheet deleted successfully', 'success');
                        },
                        error: function(xhr) {
                            showToast('Error deleting Timesheet', 'error');
                        }
                    });
                }
            });

            // Save button click handler
            $('#saveButtonTimeSheet').click(function() {
                const id = $('#timesheet_id').val();
                const isEdit = !!id;
                const formData = new FormData($('#TimesheetForm')[0]);

                let storeURL = "{{ route(getPanelRoutes('projects' . '.storeTimesheets')) }}";
                let updateURL = "{{ route(getPanelRoutes('projects' . '.updateTimesheets')) }}";

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
                        timesheetModal.hide();
                        refreshTable();
                        showToast(`Timesheet ${isEdit ? 'updated' : 'created'} successfully`,
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
