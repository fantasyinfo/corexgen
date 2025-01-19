@extends('layout.app')


@push('style')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/kanban.css') }}">
@endpush

@section('content')
    @php
        // prePrintR($stages->toArray());
    @endphp
    <div class="kanban-wrapper">

        @include('layout.components.header-buttons')
        @include('dashboard.crm.tasks.components.tasks-filters')

        <div class="row my-2">
            <div class="w-100 mx-auto">
                <x-form-components.input-group type="search" name="search" id="searchFilter"
                    placeholder="{{ __('Search... title ... ') }}" value="{{ request('search') }}" required
                    class="custom-class border-radius" />
            </div>
        </div>



        <div class="kanban-board">
            @if (isset($stages) && $stages->isNotEmpty())
                @foreach ($stages as $st)
                    <div class="kanban-column" data-status="{{ $st->id }}">
                        <div class="column-header border-bottom border-{{ $st->color }}">
                            <h5 class="column-title"> {{ $st->name }}<span
                                    class="task-count bg-{{ $st->color }} text-white">0</span>
                            </h5>
                        </div>
                        <div class="kanban-tasks" ondrop="drop(event)" ondragover="dragover(event)">
                            <!-- Tasks will be dynamically added here -->
                        </div>

                    </div>
                @endforeach
            @endif
        </div>

    </div>

    @include('dashboard.crm.tasks.components.view-modal')

@endsection

@push('scripts')
    <script>
        // Initialize the board
        $(document).ready(function() {
            loadTasks();
            updateTaskCounts();

        });

        // Load tasks into columns


        // Handle filter button click
        $(document).on("click", "#filterBtn", () => {
            loadTasks();
            // Close filter sidebar if exists
            const filterSidebar = document.getElementById("filterSidebar");
            if (filterSidebar) {
                filterSidebar.classList.remove("show");
            }
        });

        // Handle clear filter button click
        $(document).on("click", "#clearFilter", () => {
            // Reset all filter inputs
            $("[data-filter]").each(function() {
                $(this).val("");
            });

            // Reload table
            loadTasks();
        });

        // Optional: Handle enter key on filter inputs
        let debounceTimer;
        $(document).on("keypress", "[data-filter]", (e) => {
            if (e.which === 13) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadTasks();
                }, 300); // 300ms debounce
            }
        });

        $(document).on("keypress", "#searchFilter", (e) => {
            if (e.which === 13) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadTasks();
                }, 300); // 300ms debounce
            }
        });

        $(document).on("input", "#searchFilter", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadTasks();
            }, 300); // 300ms debounce
        });



        function loadTasks() {
            const urlParams = new URLSearchParams(window.location.search);
            // Dynamically append all query parameters from the URL
            const queryParams = Object.fromEntries(urlParams.entries());

            let d = {};
            $("[data-filter]").each(function() {
                d[$(this).data("filter")] = $(this).val();
            });


            d.search = $('#searchFilter').val();

            $.ajax({
                url: "{{ route(getPanelRoutes($module . '.kanbanLoad')) }}",
                method: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    query: queryParams,
                    filters: d,

                },
                success: function(data) {
                    // Clear existing tasks
                    $('.kanban-tasks').empty();

                    Object.entries(data).forEach(([stageName, leads]) => {

                        leads.forEach(lead => {

                            const taskElement = createLeadElement(lead);
                            let column = $(`.kanban-column[data-status="${lead.status_id}"]`);
                            column.find(".kanban-tasks").append(taskElement);
                        });
                    });


                    updateTaskCounts();
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                }
            });
        }

        // Create a new function to generate lead cards
        function createLeadElement(lead) {
            let baseUrl =
                "{{ route(getPanelRoutes($module . '.view'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', lead.id);

            const assigneeElements = lead.assignees.map(assignee =>
                `<img src="${assignee.profile_photo_url}" 
         alt="${assignee.name}" 
         title="${assignee.name}"
         class="rounded-circle"
         style="width: 24px; height: 24px;">`
            ).join('');

            const startDate = new Date(lead.start_date).toLocaleDateString();
            const dueDate = new Date(lead.due_date).toLocaleDateString();

            // Filter the first image file (jpg, jpeg, png, gif, webp)
            const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const firstImage = lead.attachments.find(attachment =>
                imageExtensions.includes(attachment.file_extension.toLowerCase())
            );


            let isDeletePermission = "{{ hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']) }}";
            let isViewPermissions = "{{ hasPermission(strtoupper($module) . '.' . $permissions['READ']['KEY']) }}";

            let DeleteButtonHTML = '';
            let ViewButtonHTML = '';

            // Check permissions and construct buttons
            if (isDeletePermission) {
                DeleteButtonHTML = `<i class="fas fa-trash-alt" onclick="deleteTask(${lead.id})"></i>`;
            }
            if (isViewPermissions) {
                ViewButtonHTML = `<i class="fas fa-eye" onclick="viewTask(${lead.id})"></i>`;
            }


            const imageElement = firstImage ?
                `<img src="${firstImage.file_path.replace(/\\/g, '/')}" 
             alt="${firstImage.file_name}" 
             class="rounded"
             style="width: 100%; height: auto;">` :
                '';

            return `
        <div class="task-card border-bottom border-${lead.stage.color}" 
             draggable="true" 
             ondragstart="drag(event)" 
             id="task-${lead.id}">
            <div class="task-header">
                ${lead.title}
                <div class="task-actions">
                    ${DeleteButtonHTML}
                ${ViewButtonHTML}
                </div>
            </div>
   
            <div class="task-meta">
                <div class="task-badges">
                    <span class="badge ${getPriorityClass(lead.priority)}">${lead.priority}</span>
                </div>
                <div class="assignees d-flex gap-1">
                    ${assigneeElements}
                </div>
            </div>
            
            <div class="task-attachment mt-2 border-1">
                ${imageElement}
            </div>
            
            <div class="task-footer mt-2 ">
                <div class="mb-2">
                <small class="text-muted">
                    <i class="far fa-calendar-alt"></i> 
                   Start Date:  ${startDate}
                </small>
            </div>
                <div class="mb-2">
                    <small class="text-muted">
                    <i class="far fa-calendar-alt"></i> 
                   Due Date: ${dueDate}
                </small>
                </div>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-money-bill-wave-alt"></i>
                   Billable: ${lead.billable == '1' ? 'Yes' : 'No'}
                </small>
                </div>
              
            </div>
        </div>
    `;
        }



        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.target.classList.add('dragging');
        }

        function drop(ev) {
            ev.preventDefault();
            const taskId = ev.dataTransfer.getData("text");
            const taskElement = document.getElementById(taskId);
            const targetColumn = ev.target.closest('.kanban-tasks');

            if (!targetColumn) return;

            taskElement.classList.remove('dragging');

            // Get the closest task card to the drop position
            const closestTask = getClosestTaskToDropPosition(ev.clientY, targetColumn);

            if (closestTask) {
                // If we found a closest task, insert before or after it
                const rect = closestTask.getBoundingClientRect();
                const dropPosition = ev.clientY < rect.top + rect.height / 2;

                if (dropPosition) {
                    targetColumn.insertBefore(taskElement, closestTask);
                } else {
                    targetColumn.insertBefore(taskElement, closestTask.nextSibling);
                }
            } else {
                // If no closest task found, append to the column
                targetColumn.appendChild(taskElement);
            }

            // Update task status
            const newStatus = targetColumn.closest('.kanban-column').dataset.status;
            updateTaskStatus(taskId.replace('task-', ''), newStatus);
            updateTaskCounts();
        }

        // Update task counts in column headers
        function updateTaskCounts() {
            $('.kanban-column').each(function() {
                const count = $(this).find('.task-card').length;
                $(this).find('.task-count').text(count);
            });
        }


        // Helper function to find the closest task card to the drop position
        function getClosestTaskToDropPosition(dropY, targetColumn) {
            const taskCards = [...targetColumn.querySelectorAll('.task-card:not(.dragging)')];

            return taskCards.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = dropY - (box.top + box.height / 2);

                if (closest === null || Math.abs(offset) < Math.abs(closest.offset)) {
                    return {
                        offset,
                        element: child
                    };
                }
                return closest;
            }, null)?.element;
        }

        function dragover(ev) {
            ev.preventDefault();
            const targetColumn = ev.target.closest('.kanban-tasks');

            if (!targetColumn) return;

            const draggingCard = document.querySelector('.dragging');
            if (!draggingCard) return;

            const closestTask = getClosestTaskToDropPosition(ev.clientY, targetColumn);

            // Remove any existing drop indicators
            const indicators = targetColumn.querySelectorAll('.drop-indicator');
            indicators.forEach(indicator => indicator.remove());

            if (closestTask) {
                const rect = closestTask.getBoundingClientRect();
                const dropPosition = ev.clientY < rect.top + rect.height / 2;

                const indicator = document.createElement('div');
                indicator.className = 'drop-indicator';

                if (dropPosition) {
                    targetColumn.insertBefore(indicator, closestTask);
                } else {
                    targetColumn.insertBefore(indicator, closestTask.nextSibling);
                }
            }
        }




        // Delete task
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                $(`#task-${taskId}`).remove();


                let baseUrl =
                    "{{ route(getPanelRoutes($module . '.destroy'), ['id' => ':id']) }}";
                let url = baseUrl.replace(':id', taskId);


                $.ajax({
                    url: url,
                    method: "POST",
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        console.log(response);
                        console.log('Task deleted successfully');
                    },
                    error: function(xhr) {
                        console.error('Error updating task status:', xhr.responseText);
                    }
                });


                updateTaskCounts();

            }
        }

        // Update task status
        function updateTaskStatus(leadid, stageid) {
            let baseUrl =
                "{{ route(getPanelRoutes($module . '.changeStage'), ['leadid' => ':leadid', 'stageid' => ':stageid']) }}";
            let url = baseUrl.replace(':leadid', leadid).replace(':stageid', stageid);


            $.ajax({
                url: url,
                method: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'GET',
                    from_kanban: true,
                },
                success: function(response) {
                    console.log(response);
                    console.log('Task status updated successfully');
                },
                error: function(xhr) {
                    console.error('Error updating task status:', xhr.responseText);
                }
            });

            //loadTasks();
        }

        // function viewTask(taskId) {

        //     let baseUrl =
        //         "{{ route(getPanelRoutes($module . '.kanbanView'), ['id' => ':id']) }}";
        //     let url = baseUrl.replace(':id', taskId);

        //     $.ajax({
        //         url: url,
        //         method: "GET",
        //         data: {
        //             _token: '{{ csrf_token() }}',
        //         },
        //         success: function(response) {
        //             console.log(response);
        //             populateViewModal(response);

        //             $('#viewLeadModal').modal('show');
        //         },
        //         error: function(xhr) {
        //             console.error('Error fetching  data:', xhr.responseText);
        //         }
        //     });


        // }



        function viewTask(taskId) {
            let baseUrl = "{{ route(getPanelRoutes($module . '.view'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', taskId);

            $.get(`${url}?fromkanban=true`, function(response) {
                // Add styles if not already present
                if (!document.getElementById('modal-dynamic-styles')) {
                    const styleSheet = document.createElement('style');
                    styleSheet.id = 'modal-dynamic-styles';
                    styleSheet.textContent = response.styles;
                    document.head.appendChild(styleSheet);
                }

                // Add the HTML content
                const modalContainer = document.getElementById('task-detail-container');
                modalContainer.innerHTML = response.html;

                // Show the modal
                openTaskModal();

                // Add scripts after modal is shown
                if (!document.getElementById('modal-dynamic-scripts')) {
                    const scriptElement = document.createElement('script');
                    scriptElement.id = 'modal-dynamic-scripts';
                    scriptElement.textContent = response.scripts;
                    document.body.appendChild(scriptElement);

                    // Re-initialize any event listeners or plugins
                    initializeModalScripts();
                }
            });
        }

        function initializeModalScripts() {
            // Re-initialize your modal-specific scripts
            $("#editToggle").click(function(e) {
                e.preventDefault();
                $("#viewDetails").toggle();
                $("#editDetails").toggle();
                $("#updateBtn").toggle();


            });

            // Check and initialize DataTable if needed
            if ($.fn.DataTable && $(".daTableQuick").length && !$.fn.DataTable.isDataTable(".daTableQuick")) {
                new DataTable(".daTableQuick", {});
            }

            if (typeof $ !== "undefined" && $(".searchSelectBox").length > 0) {
                $(".searchSelectBox").select2({
                    placeholder: "Please select an option",
                    minimumResultsForSearch: 5,
                });
            }

            const dateTimeInput = document.querySelectorAll(
                'input[type="datetime-local"]'
            );
            if (dateTimeInput.length > 0 && typeof flatpickr !== "undefined") {
                dateTimeInput.forEach((input) => {
                    flatpickr(input, {
                        enableTime: true,
                        altInput: true,
                        defaultDate: input.value,
                    });
                });
            }


            // Dynamically include footer JS if needed

            // console.log("Footer scripts loaded:", document.getElementById("footer-js-links")?.innerHTML);
            loadCommentsScripts();
            loadAttachmentsScripts();
            loadTimeSheetScripts();
            // Add any other initialization code here
        }

        function openTaskModal() {
            const modal = new bootstrap.Modal(document.getElementById('task-detail-modal'), {
                keyboard: true,
                backdrop: 'static'
            });

            // Clean up when modal is hidden
            modal._element.addEventListener('hidden.bs.modal', function() {
                // Remove dynamic styles and scripts if needed
                const dynamicStyles = document.getElementById('modal-dynamic-styles');
                const dynamicScripts = document.getElementById('modal-dynamic-scripts');

                if (dynamicStyles) dynamicStyles.remove();
                if (dynamicScripts) dynamicScripts.remove();
            });

            modal.show();
        }

        function closeTaskModal() {
            const modal = document.getElementById('task-detail-modal');
            modal.classList.remove('visible');
        }

        function loadTimeSheetScripts() {
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

                    let baseUrl =
                        "{{ route(getPanelRoutes('projects' . '.editTimesheets'), ['id' => ':id']) }}";
                    let editURL = baseUrl.replace(':id', id);
                    // Fetch Timesheet data
                    $.ajax({
                        url: `${editURL}`,
                        method: 'GET',
                        success: function(response) {
                            $('#modalTitle').text('Edit Timesheet');
                            $('#timesheet_id').val(response.id);
                            $('#start_date_t').val(new Date(response.start_date).toISOString()
                                .slice(
                                    0, 16));
                            $('#end_date_t').val(new Date(response.end_date).toISOString()
                                .slice(0,
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
                            showToast(
                                `Timesheet ${isEdit ? 'updated' : 'created'} successfully`,
                                'success');
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                showErrors(errors);

                                // Display errors in an alert box
                                let errorMessages = '';
                                for (const [field, messages] of Object.entries(errors)) {
                                    errorMessages += `${field}: ${messages.join(', ')}\n`;
                                }

                                console.log(
                                    errors); // Keep the console log for debugging purposes
                                alert(errorMessages);

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
        }

        function loadCommentsScripts() {
            $('#commentForm').submit(function(e) {
                e.preventDefault();

                if (typeof tinymce !== 'undefined' && tinymce.get('comment')) {
                    const content = tinymce.get('comment').getContent();
                    if (!content.trim()) {
                        alert('Please enter a comment');
                        return;
                    }
                    $('textarea[name="comment"]').val(content);
                }


                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {

                        let deleteURL =
                            "{{ route(getPanelRoutes('tasks.comment.destroy'), ['id' => ':id']) }}";
                        deleteURL = deleteURL.replace(':id', response.comment.id);

                        let isDeletePermission =
                            "{{ hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']) }}";

                        // console.log(isDeletePermission)
                        // Add the new comment to the UI
                        const deleteButton = isDeletePermission ?
                            `<div class="action-buttons">
                                    <button class="btn btn-danger btn-sm delete-comment" data-id="${response.comment.id}" data-url="${deleteURL}">Delete</button>
                            </div>` :
                            '';

                        $('.note-list').prepend(`
    <div class="comment-item mb-4" data-id="${response.comment.id}">
        <div class="d-flex">
            <div class="comment-avatar">
                ${response.user.profile_photo_path ? 
                    `<img src="${response.user.profile_photo_path}" alt="Avatar" class="rounded-circle">` : 
                    `<div class="default-avatar"><i class="fas fa-user"></i></div>`}
            </div>
            <div class="comment-content flex-grow-1 ms-3">
                <div class="comment-header text-muted d-flex justify-content-between align-items-center">
                    <h6 class="comment-author mb-0">
                        ${response.user.name}
                        <small class="text-muted ms-2"><i class="far fa-clock"></i> Just now</small>
                    </h6>
                    ${deleteButton}
                </div>
                <div class="comment-body mt-2">
                    <p class="mb-0">${response.comment.comment}</p>
                </div>
            </div>
        </div>
    </div>
`);


                        // Clear the form
                        $('#commentForm')[0].reset();
                        alert('Comment added successfully', 'success');
                    },
                    error: function(xhr) {
                        alert('An error occurred while adding the comment.');
                    },
                });
            });

            $(document).on('click', '.delete-comment', function() {
                const button = $(this);
                const url = button.data('url');

                if (confirm('Are you sure you want to delete this comment?')) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function() {
                            button.closest('.comment-item').remove();
                            alert('Comment deleted successfully', 'success');
                        },
                        error: function() {
                            alert('An error occurred while deleting the comment.');
                        },
                    });
                }
            });

            // Initialize WYSIWYG editor
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.wysiwyg-editor-comment',
                    height: 300,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    menubar: false,
                    setup: function(editor) {
                        editor.on('change', function() {
                            editor.save(); // This automatically updates the textarea
                        });
                    },
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        'autoresize',
                        'autosave',
                        'charmap',
                        'code',
                        'codesample',
                        'directionality',
                        'emoticons',
                        'fullscreen',
                        'help',
                        'lists',
                        'link',
                        'image',


                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',


                        'insertdatetime',
                        'media',
                        'table',



                        'wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \alignleft aligncenter alignright alignjustify | \bullist numlist outdent indent | removeformat | help | \link image media preview codesample table code'
                });
            }
        }

        function loadAttachmentsScripts() {
            $(document).ready(function() {
                // Initialize variables
                let selectedFiles = [];
                const fileUploadInput = $('#fileUpload');
                const fileListContainer = $('#fileList');
                const fileUploadArea = $('.file-upload-area');
                const maxFileSize = 10 * 1024 * 1024; // 10MB limit
                const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token properly

                // Handle file selection
                fileUploadInput.on('change', function() {
                    addFiles(this.files);
                });

                // Drag-and-drop support
                fileUploadArea
                    .on('dragover', function(e) {
                        e.preventDefault();
                        $(this).addClass('drag-over');
                    })
                    .on('dragleave drop', function(e) {
                        e.preventDefault();
                        $(this).removeClass('drag-over');
                        if (e.type === 'drop') {
                            addFiles(e.originalEvent.dataTransfer.files);
                        }
                    });

                // Add files to the list with validation
                function addFiles(files) {
                    Array.from(files).forEach((file) => {
                        // Check file size
                        if (file.size > maxFileSize) {
                            alert(
                                `File ${file.name} is too large. Maximum size is ${maxFileSize/1024/1024}MB`
                            );
                            return;
                        }

                        // Check for duplicates
                        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                            selectedFiles.push(file);
                        }
                    });
                    renderFileList();
                }

                // Render file list
                function renderFileList() {
                    fileListContainer.empty();
                    selectedFiles.forEach((file, index) => {
                        fileListContainer.append(`
                        <li class="file-item">
                            <div>
                                <span class="file-name">${truncateFileName(file.name, 30)}</span>
                                <span class="file-size">(${(file.size / 1024).toFixed(2)} KB)</span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-file" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </li>
                    `);
                    });
                }

                // Remove file from the list
                fileListContainer.on('click', '.remove-file', function() {
                    const index = $(this).data('index');
                    selectedFiles.splice(index, 1);
                    renderFileList();
                });

                // Truncate long file names
                function truncateFileName(name, maxLength) {
                    return name.length > maxLength ? `${name.substring(0, maxLength - 3)}...` : name;
                }

                // Handle file upload via AJAX
                $('#mediaForm').submit(function(e) {
                    e.preventDefault();

                    if (selectedFiles.length === 0) {
                        alert('Please select files to upload');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', csrfToken); // Add CSRF token to FormData
                    formData.append('id', $('input[name="id"]').val());
                    selectedFiles.forEach(file => formData.append('files[]', file));

                    // Disable submit button during upload
                    const submitBtn = $(this).find('button[type="submit"]');
                    submitBtn.prop('disabled', true);

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken // Add CSRF token to headers
                        },
                        success: function(response) {




                            let isDeletePermission =
                                "{{ hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']) }}";

                            response.attachments.forEach((attachment) => {
                                let deleteURL =
                                    "{{ route(getPanelRoutes('tasks.attachment.destroy'), ['id' => ':id']) }}";
                                deleteURL = deleteURL.replace(':id', attachment.id);

                                const deleteButton = isDeletePermission ?
                                    `<button class="btn btn-danger btn-sm delete-attachment" data-url="${deleteURL}">
                                      <i class="fas fa-trash"></i>
                                   </button>` :
                                    '';

                                $('.note-list').prepend(`
                                <div class="attachment-item mb-4" data-id="${attachment.id}">
                                    <div class="d-flex">
                                        <div class="attachment-icon">
                                            ${attachment.file_extension.match(/(jpg|jpeg|png|gif|webp)/i)
                                                ? `<img src="${attachment.file_path}" alt="${attachment.file_name}" class="attachment-preview" />`
                                                : `<i class="fas fa-file"></i>`}
                                        </div>
                                        <div class="attachment-content ms-3">
                                            <div class="attachment-header text-muted d-flex justify-content-between align-items-center">
                                                <h6 class="attachment-name mb-0">
                                                    ${attachment.file_name}
                                                    <small class="text-muted ms-2">(${(attachment.size / 1024).toFixed(2)} KB)</small>
                                                </h6>
                                                <div class="attachment-actions">
                                                    <a href="${attachment.file_path}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="${attachment.file_path}" download class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    ${deleteButton}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                            });

                            selectedFiles = [];
                            renderFileList();
                            alert('Files uploaded successfully.');
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message ||
                                'An error occurred during upload.');
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false);
                        }
                    });
                });

                // Delete attachment via AJAX
                $(document).on('click', '.delete-attachment', function() {
                    const button = $(this);
                    const url = button.data('url');

                    if (confirm('Are you sure you want to delete this attachment?')) {
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: csrfToken
                            },
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            success: function() {
                                button.closest('.attachment-item').remove();
                                alert('Attachment deleted successfully.');
                            },
                            error: function(xhr) {
                                alert(xhr.responseJSON?.message ||
                                    'An error occurred while deleting the attachment.');
                            }
                        });
                    }
                });
            });
        }

        function populateViewModal(data) {
            const lead = data.lead;

            // Basic Information
            $('#viewLeadModal #billable').text(lead.billable == '1' ? 'Yes' : 'No');
            $('#viewLeadModal #title').text(lead.title);
            $('#viewLeadModal #relatedTo').text(lead.related_to.toUpperCase());
            $('#viewLeadModal #hourlyRate').text(`${parseFloat(lead.hourly_rate).toLocaleString()}`);

            // Status & Priority
            $('#viewLeadModal #stage').html(`<span class="badge bg-${lead.stage.color}" >${lead.stage.name}</span>`);
            $('#viewLeadModal #priority').html(
                `<span class="badge bg-${getPriorityClass(lead.priority)}" >${lead.priority}</span>`);


            // Dates
            $('#viewLeadModal #startDate').text(formatDate(lead.start_date));
            $('#viewLeadModal #dueDate').text(formatDate(lead.due_date));


            // tabs

            $('#viewLeadModal  #detailsView').html(lead.description);


            let baseUrl =
                "{{ route(getPanelRoutes($module . '.view'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', lead.id);

            $("#details_view_link").attr('href', url);


            // Assignees
            const assigneesContainer = $('#viewLeadModal #assigneesList');
            assigneesContainer.empty();

            lead.assignees.forEach(assignee => {
                assigneesContainer.append(`
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <img src="${assignee.profile_photo_url}" 
                             alt="${assignee.name}" 
                             class="rounded-circle mb-2"
                             style="width: 64px; height: 64px;">
                        <h6 class="card-title mb-0">${assignee.name}</h6>
                    </div>
                </div>
            </div>
        `);
            });
        }

        // Helper function to format dates
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Helper function to get priority badge class
        function getPriorityClass(priority) {
            switch (priority.toLowerCase()) {
                case 'high':
                    return 'bg-danger';
                case 'medium':
                    return 'bg-warning';
                case 'low':
                    return 'bg-info';
                case 'urgent':
                    return 'bg-primary';
                default:
                    return 'bg-secondary';
            }
        }
    </script>
@endpush
