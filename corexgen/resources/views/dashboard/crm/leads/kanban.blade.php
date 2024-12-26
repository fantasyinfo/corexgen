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
        @include('dashboard.crm.leads.components.leads-filters')

        <div class="kanban-board">
            @if (isset($stages) && $stages->isNotEmpty())
                @foreach ($stages as $st)
                    <div class="kanban-column" data-status="{{ $st->id }}">
                        <div class="column-header " style="border-bottom: 2px solid {{ $st->color }};">
                            <h5 class="column-title"> {{ $st->name }}<span class="task-count">0</span>
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
    @include('dashboard.crm.leads.components.edit-modal')
@endsection

@push('scripts')

    <script>
        // Initialize the board
        $(document).ready(function() {
            loadTasks();
            updateTaskCounts();

            $('#clientType').on('change', function() {
                var selectedType = $(this).val();
                if (selectedType === 'Company') {
                    $('#company_name_div').show();
                } else {
                    $('#company_name_div').hide();
                }
            });

            // Trigger change event on page load
            $('#clientType').trigger('change');

        });

        // Load tasks into columns

        function loadTasks() {
            $.ajax({
                url: "{{ route(getPanelRoutes($module . '.kanbanLoad')) }}",
                method: "GET",
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    // Clear existing tasks
                    $('.kanban-tasks').empty();

                    Object.entries(data).forEach(([stageName, leads]) => {
                        // console.log(`Processing stage: ${stageName} with ${leads.length} leads.`);
                        leads.forEach(lead => {
                            // console.log(`Processing lead: ${lead.id} - ${lead.company_name}`);

                            const taskElement = createLeadElement(lead);
                            // console.log("Generated taskElement:", taskElement);

                            let column = $(`.kanban-column[data-status="${lead.status_id}"]`);
                            //                 if (!column.length) {
                            //                     console.warn(
                            //                         `Column not found for status_id: ${lead.status_id}. Creating it.`
                            //                     );
                            //                     column = $(`
                        //     <div class="kanban-column" data-status="${lead.status_id}">
                        //         <h3>Stage ${lead.status_id}</h3>
                        //         <div class="kanban-tasks"></div>
                        //     </div>
                        // `);
                            //                     $(".kanban-board").append(column);
                            //                 }

                            // console.log("Appending to column:", column);
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
            // console.log(lead)
            return `
        <div class="task-card" draggable="true" ondragstart="drag(event)" id="task-${lead.id}" 
             style="border-left: 1px solid ${lead.stage.color}">
            <div class="task-header">
                <h6 class="task-title">${lead.company_name}</h6>
                <div class="task-actions">
                    <i class="fas fa-edit" onclick="editTask(${lead.id})"></i>
                    <i class="fas fa-trash-alt" onclick="deleteTask(${lead.id})"></i>
                </div>
            </div>
            <p class="mb-2 text-muted small">${lead.first_name} ${lead.last_name}</p>
            <div class="task-meta">
                <div class="task-badges">
                    <span class="badge" style="background-color: ${lead.group.color}">${lead.group.name}</span>
                    <span class="badge" style="background-color: ${lead.source.color}">${lead.source.name}</span>
                </div>
                <div class="assignees d-flex gap-1">
                    ${lead.assignees.map(assignee => `
                                                                                                                <img src="${assignee.profile_photo_url}" 
                                                                                                                     alt="${assignee.name}" 
                                                                                                                     title="${assignee.name}"
                                                                                                                     class="rounded-circle"
                                                                                                                     style="width: 24px; height: 24px;">
                                                                                                            `).join('')}
                </div>
            </div>
            <div class="task-footer mt-2 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="far fa-calendar-alt"></i> 
                    ${new Date(lead.created_at).toLocaleDateString()}
                </small>
                <small class="badge ${lead.status === 'ACTIVE' ? 'bg-success' : 'bg-danger'}">
                    ${lead.status}
                </small>
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


        // Edit task
        function editTask(taskId) {
            // In real application, you would show a modal here
            console.log('Editing task:', taskId);
            let baseUrl =
                "{{ route(getPanelRoutes($module . '.kanbanEdit'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', taskId);

            $.ajax({
                url: url,
                method: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    console.log(response);
                    populateEditModal(response);
                    $('#editLeadModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error fetching  data:', xhr.responseText);
                }
            });


        }




        function populateEditModal(data) {
            const lead = data.lead;
            const form = $('#leadEditForm');

            // Reset form
            form[0].reset();

            // Set hidden ID field
            form.find('input[name="id"]').val(lead.id);

            // Populate basic fields
            form.find('select[name="type"]').val(lead.type).trigger('change');
            form.find('input[name="company_name"]').val(lead.company_name);
            form.find('input[name="first_name"]').val(lead.first_name);
            form.find('input[name="last_name"]').val(lead.last_name);
            form.find('input[name="title"]').val(lead.title);
            form.find('input[name="value"]').val(lead.value);
            form.find('select[name="priority"]').val(lead.priority);
            form.find('input[name="email"]').val(lead.email);
            form.find('input[name="phone"]').val(lead.phone);
            form.find('select[name="preferred_contact_method"]').val(lead.preferred_contact_method);

            // Populate dropdowns
            form.find('select[name="status_id"]').val(lead.status_id);
            form.find('select[name="group_id"]').val(lead.group_id);
            form.find('select[name="source_id"]').val(lead.source_id);

            // Populate assignees (multi-select)
            const assigneeIds = lead.assignees.map(assignee => assignee.id);
            form.find('select[name="assign_to[]"]').val(assigneeIds).trigger('change');

            // Populate address fields if exists
            if (lead.address) {
                form.find('textarea[name="address[street_address]"]').val(lead.address.street_address);
                form.find('select[name="address[country_id]"]').val(lead.address.country_id).trigger('change');
                form.find('input[name="address[city_name]"]').val(lead.address.city?.name);
                form.find('input[name="address[pincode]"]').val(lead.address.postal_code);
            }


            // Populate TinyMCE editor
            if (tinymce.get('details')) {

                tinymce.get('details').setContent(lead.details || '');

            }

            // Populate custom fields if they exist
            if (data.customFields && data.customFields.length > 0) {
                data.customFields.forEach(field => {
                    const fieldValue = data.cfOldValues[field.field_name] || '';
                    const fieldElement = form.find(`[name="custom_fields[${field.field_name}]"]`);

                    if (fieldElement.length) {
                        if (field.field_type === 'checkbox') {
                            fieldElement.prop('checked', fieldValue === '1');
                        } else if (field.field_type === 'select' && fieldElement.hasClass('select2')) {
                            fieldElement.val(fieldValue).trigger('change');
                        } else {
                            fieldElement.val(fieldValue);
                        }
                    }
                });
            }


        }


      
          // Initialize WYSIWYG editor
          if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.wysiwyg-editor',
                    height: 300,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! old('details', '') !!}`);
                        });
                    },
                    menubar: false,
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
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                                                                                  alignleft aligncenter alignright alignjustify | \
                                                                                                  bullist numlist outdent indent | removeformat | help | \
                                                                                                  link image media preview codesample table'
                });
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
                method: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT'
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
    </script>
@endpush
