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

        <div class="row my-2">
            <div class="w-100 mx-auto">
                <x-form-components.input-group type="search" name="search" id="searchFilter"
                    placeholder="{{ __('Search... type, name, company name, title, email, phone ... ') }}"
                    value="{{ request('search') }}" required class="custom-class border-radius" />
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

    @include('dashboard.crm.leads.components.view-modal')

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


            const nameElement = lead.type === 'Company' ?
                `<a href='${url}'><h6 class="task-title"> <span class="status-circle bg-success me-2"></span> ${lead.company_name}</h6></a>` :
                `<a href='${url}'><h6 class="task-title"> <span class="status-circle bg-warning me-2"></span> ${lead.first_name}</h6></a>`;

            const assigneeElements = lead.assignees.map(assignee =>
                `<img src="${assignee.profile_photo_url}" 
             alt="${assignee.name}" 
             title="${assignee.name}"
             class="rounded-circle"
             style="width: 24px; height: 24px;">`
            ).join('');

            const statusClass = lead.status === 'ACTIVE' ? 'bg-success' : 'bg-danger';
            const formattedDate = new Date(lead.created_at).toLocaleDateString();

         

            return `
        <div class="task-card border-bottom border-${lead.stage.color}" 
             draggable="true" 
             ondragstart="drag(event)" 
             id="task-${lead.id}">
            <div class="task-header">
                ${nameElement}
   
                <div class="task-actions">
                 
                    <i class="fas fa-trash-alt " onclick="deleteTask(${lead.id})"></i>
                </div>
            </div>
            <p class="mb-2 text-muted small">${lead.first_name} ${lead.last_name}</p>
            <div class="task-meta">
                <div class="task-badges">
                    <span class="badge bg-${lead.group.color}">${lead.group.name}</span>
                    <span class="badge bg-${lead.source.color}">${lead.source.name}</span>
                </div>
                <div class="assignees d-flex gap-1">
                    ${assigneeElements}
                </div>
            </div>
            <div class="task-footer mt-2 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="far fa-calendar-alt"></i> 
                    ${formattedDate}
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
    </script>
@endpush
