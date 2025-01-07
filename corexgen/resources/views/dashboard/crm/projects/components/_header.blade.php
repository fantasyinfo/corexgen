<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">

            <div>
                <h1 class="mb-1">

                    {{ $project->title }}

                </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-money me-1"></i> {{ $project->billing_type }}
                    </span>
                    <span class="badge bg-{{ CRM_STATUS_TYPES['PROJECTS']['BT_CLASSES'][$project->status] }}">
                        {{ $project->status }}
                    </span>

                    <span class="lead-score" data-bs-toggle="tooltip" title="Progress">
                        {{ $project->progress ?? 0 }} <i class="fas fa-star text-warning"></i>
                    </span>
                    <p class="mt-2">
                        @foreach ($project->assignees as $user)
                            <a style="text-decoration: none;"
                                href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                                <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))"
                                    :title="$user->name" />
                            </a>
                        @endforeach
                        <x-form-components.add-assignee 
                        :action="route(getPanelRoutes('projects.addAssignee'))" :modal="$project" :teamMates="$teamMates"
                            :hw="40" />

                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
