<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Tasks Lists</h5>
</div>

<div class="timeline-wrapper">
    @if ($tasks && $tasks->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>{{ $task->id }}</td>
                            <td>{!! "<a class='dt-link' href='" .
                                route(getPanelRoutes('tasks.view'), $task->id) .
                                "' target='_blank'>$task->title</a>" !!}</td>
                            <td><span class="badge bg-{{ $task?->stage?->color }}">{{ $task?->stage?->name }}</span></td>
                            <td>{{ $task?->assignedBy?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h6>No Tasks Yet</h6>
            <p class="text-muted">Tasks will appear here, if any.</p>
        </div>
    @endif
</div>
