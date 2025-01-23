{{-- modal-component.blade.php --}}
@props([
    'hw' => '60',
    'title' => 'add new +',
    'action' => '',
    'modal' => '',
    'teamMates' => '',
])

<button type="button" onclick="openAssigneeModal()" data-bs-toggle="tooltip" title="add new user" class="link rounded-circle border border-3"
    style="width: {{ $hw }}px; height: {{ $hw }}px; object-fit: cover; border-color: var(--primary-color)">
    <i class="fas fa-user-plus" style="color: var(--primary-color)"></i>
</button>

<div id="modalBackdrop" class="fixed inset-0 hidden" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1040;"></div>

<div id="assigneeModal" class="fixed hidden"
    style="z-index: 1050; left: 50%; top: 50%; transform: translate(-50%, -50%);">
    <div class="modal-wrapper" style="min-width: 320px; max-width: 400px;">
        <div class="modal-content"
            style="
            background: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        ">
            <form id="assigneeForm" action="{{ $action }}" method="POST">
                @csrf
                <div class="modal-header"
                    style="
                    padding: 16px 20px;
                    border-bottom: 1px solid var(--border-color);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h5
                        style="
                        margin: 0;
                        color: var(--body-color);
                        font-size: 1rem;
                        font-weight: 600;
                    ">
                        Select Team Members</h5>
                    <button type="button" onclick="closeAssigneeModal()"
                        style="
                            background: none;
                            border: none;
                            color: var(--neutral-gray);
                            cursor: pointer;
                            padding: 4px;
                        ">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body" style="padding: 20px;">
                    <input type="hidden" name="id" value="{{ $modal->id }}" />
                    <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates" :name="'assign_to'"
                        :multiple="true" :selected="$modal->assignees->pluck('id')->toArray()" />
                </div>

                <div class="modal-footer"
                    style="
                    padding: 16px 20px;
                    border-top: 1px solid var(--border-color);
                    display: flex;
                    justify-content: flex-end;
                    gap: 8px;
                ">
                    <button type="button" onclick="closeAssigneeModal()"
                        style="
                            padding: 8px 16px;
                            border-radius: 6px;
                            border: 1px solid var(--border-color);
                            background: var(--input-bg);
                            color: var(--body-color);
                            cursor: pointer;
                            font-size: 0.875rem;
                        ">
                        Cancel
                    </button>
                    <button type="submit"
                        style="
                            padding: 8px 16px;
                            border-radius: 6px;
                            border: none;
                            background: var(--primary-color);
                            color: white;
                            cursor: pointer;
                            font-size: 0.875rem;
                        ">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- making changes on the script tag also make changes on the kanban.blade.php files for tasks, and leads --}}
@push('scripts')
    <script>
        function openAssigneeModal() {
            document.getElementById('modalBackdrop').classList.remove('hidden');
            document.getElementById('assigneeModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAssigneeModal() {
            document.getElementById('modalBackdrop').classList.add('hidden');
            document.getElementById('assigneeModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close modal when clicking outside
        document.getElementById('modalBackdrop').addEventListener('click', closeAssigneeModal);

        // Close modal with escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAssigneeModal();
            }
        });

        // Prevent modal from closing when clicking inside the modal content
        document.querySelector('#assigneeModal .modal-content').addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Handle AJAX form submission
        $('#assigneeForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            const form = this;
            const formData = new FormData(form);

            // Disable submit button to prevent multiple submissions
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerText = 'Saving...';

            $.ajax({
                url: $(form).attr('action'),
                method: 'POST',
                data: formData,
                processData: false, // Required for FormData
                contentType: false, // Required for FormData
                success: function(response) {
                    // Handle success
                    alert('Team members assigned successfully!, Reload the page to view new assingees');
                    closeAssigneeModal();

                    // Optionally update the UI dynamically
                    // For example, refresh a list of team members
                    // $('#assigneeList').html(response.updatedHTML); // Example
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error('Error:', xhr.responseText);
                    alert('An error occurred while assigning team members.');
                },
                complete: function() {
                    // Re-enable the submit button
                    submitButton.disabled = false;
                    submitButton.innerText = 'Save';
                }
            });
        });
    </script>
@endpush
@push('style')
    <style>
        .fixed {
            position: fixed;
        }

        .inset-0 {
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }

        .hidden {
            display: none;
        }

        /* Hover effects */
        .modal-footer button:hover {
            opacity: 0.9;
            transition: opacity 0.2s ease;
        }

        /* Button focus states */
        .modal-footer button:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }

        /* Smooth transitions */
        #assigneeModal {
            transition: opacity 0.2s ease;
        }

        #modalBackdrop {
            transition: opacity 0.2s ease;
        }
    </style>
@endpush
