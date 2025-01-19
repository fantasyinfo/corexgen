<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Tasks Lists</h5>
    <a href="{{ route(getPanelRoutes('tasks.create')) }}?type=project&id={{ $project->id }}&refrer=projects.view"
        class="btn btn-primary">  <i class="fas fa-plus"></i> Create Task</a>
</div>

<div class="timeline-wrapper">
    @if ($tasks && $tasks->isNotEmpty())
        <div class="table-responsive " >
            <table class="table p-3  table-bordered ui daTableQuick" >
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Assign To</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>{!! "<a class='dt-link' href='" .
                                route(getPanelRoutes('tasks.view'), $task->id) .
                                "' target='_blank'>$task->title</a>" !!}</td>
                            <td>
                                @foreach ($task->assignees as $user)
                                    <a href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                                        <x-form-components.profile-avatar :hw="40" :url="asset(
                                            'storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'),
                                        )"
                                            :title="$user->name" />
                                    </a>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $task?->stage?->color }}"> {{ $task?->stage?->name }}</span>
                            </td>
                            <td>{{ formatDateTime($task?->start_date) }}</td>
                            <td>{{ $task?->due_date ? formatDateTime($task?->due_date) : 'Not Specified' }}</td>
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
