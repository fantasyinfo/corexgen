@extends('layout.app')

<link rel="stylesheet" type="text/css" href="{{ asset('css/custom/kanban.css') }}">
@push('style')
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
                    <div class="kanban-column" style="border: 1px solid {{ $st->color }}" data-status="{{ $st->id }}">
                        <div class="column-header " style="border-bottom: 2px solid {{ $st->color }};">
                            <h5 class="column-title" style="color:  {{ $st->color }};"> {{ $st->name }}<span
                                    class="task-count">0</span>
                            </h5>
                        </div>
                        <div class="kanban-tasks" ondrop="drop(event)" ondragover="allowDrop(event)">
                            <!-- Tasks will be dynamically added here -->
                        </div>
                        <div class="p-3">
                            <button class="add-task-btn" onclick="addTaskToColumn('{{ $st->name }}')">
                                <i class="fas fa-plus"></i> Add Task
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // Initialize the board
        $(document).ready(function() {
            loadTasks();
            updateTaskCounts();
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

                    // Loop through each stage and its leads
                    Object.entries(data).forEach(([stageName, leads]) => {
                        leads.forEach(lead => {
                            const taskElement = createLeadElement(lead);
                            $(`.kanban-column[data-status="${lead.status_id}"] .kanban-tasks`)
                                .append(taskElement);
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
            return `
        <div class="task-card" draggable="true" ondragstart="drag(event)" id="task-${lead.id}" 
             style="border-left: 4px solid ${lead.stage.color}">
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

        // Drag and drop functionality
        function allowDrop(ev) {
            ev.preventDefault();
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

            console.log(targetColumn);
            if (targetColumn) {
                taskElement.classList.remove('dragging');
                targetColumn.appendChild(taskElement);

                // Update task status
                const newStatus = targetColumn.closest('.kanban-column').dataset.status;

                updateTaskStatus(taskId.replace('task-', ''), newStatus);
                updateTaskCounts();
            }
        }

        // Update task counts in column headers
        function updateTaskCounts() {
            $('.kanban-column').each(function() {
                const count = $(this).find('.task-card').length;
                $(this).find('.task-count').text(count);
            });
        }

        // Add new task
        function addNewTask() {
            const newTask = {
                id: Date.now(),
                title: 'New Task',
                description: 'Task description',
                status: 'todo',
                priority: 'medium',
                assignee: 'Unassigned',
                dueDate: '2024-01-30'
            };

            const taskElement = createTaskElement(newTask);
            $('.kanban-column[data-status="todo"] .kanban-tasks').append(taskElement);
            updateTaskCounts();

            // In real application, you would make an AJAX call here
            // $.ajax({
            //     url: '/api/tasks',
            //     method: 'POST',
            //     data: newTask,
            //     success: function(response) {
            //         console.log('Task created successfully');
            //     }
            // });
        }

        // Add task to specific column
        function addTaskToColumn(status) {
            const newTask = {
                id: Date.now(),
                title: 'New Task',
                description: 'Task description',
                status: status,
                priority: 'medium',
                assignee: 'Unassigned',
                dueDate: '2024-01-30'
            };

            const taskElement = createTaskElement(newTask);
            $(`.kanban-column[data-status="${status}"] .kanban-tasks`).append(taskElement);
            updateTaskCounts();
        }

        // Edit task
        function editTask(taskId) {
            // In real application, you would show a modal here
            console.log('Editing task:', taskId);

            // $.ajax({
            //     url: `/api/tasks/${taskId}`,
            //     method: 'PUT',
            //     data: updatedTask,
            //     success: function(response) {
            //         console.log('Task updated successfully');
            //     }
            // });
        }

        // Delete task
        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                $(`#task-${taskId}`).remove();
                updateTaskCounts();

                // In real application, you would make an AJAX call here
                // $.ajax({
                //     url: `/api/tasks/${taskId}`,
                //     method: 'DELETE',
                //     success: function(response) {
                //         console.log('Task deleted successfully');
                //     }
                // });
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
        }
    </script>
@endpush
