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

                        <div class="flex flex-col justify-content-center align-items-center ">
                            <div class="progress" style="width:300px;"  data-bs-toggle="tooltip" title="Progress">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ $project?->progress }}%;" aria-valuenow="{{ $project?->progress }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    {{ $project?->progress }}%
                                </div>
                              
                            </div>
                            <span class="font-12">Progess is calculates based on tasks completed</span>
                        </div>
                       

              
                    <p class="mt-2">
                        @foreach ($project->assignees as $user)
                            <a style="text-decoration: none;"
                                href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                                <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))"
                                    :title="$user->name" />
                            </a>
                        @endforeach
                        <x-form-components.add-assignee :action="route(getPanelRoutes('projects.addAssignee'))" :modal="$project" :teamMates="$teamMates"
                            :hw="40" />

                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
