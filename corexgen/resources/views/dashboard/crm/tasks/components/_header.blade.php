<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1 class="mb-1">
                    {{ $task->title }}
                </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $task->priority }} Priority
                    </span>
                    <span class="badge bg-{{ $task->stage->color }}">
                        {{ $task->stage->name }}
                    </span>
                    @if ($task->billable)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Billable
                        </span>
                    @endif

                </div>
                <p class="mt-2">
                    @foreach ($task->assignees as $user)
                        <a style="text-decoration: none;"
                            href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                            <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" :title="$user->name" />
                        </a>
                    @endforeach
                    <x-form-components.add-assignee :action="route(getPanelRoutes('tasks.addAssignee'))" :modal="$task" :teamMates="$teamMates"
                        :hw="40" />

                </p>
            </div>
        </div>
    </div>

</div>
