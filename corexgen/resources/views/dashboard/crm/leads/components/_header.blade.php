<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div class="lead-avatar">
                @if ($lead->type == 'Company')
                    <div class="company-avatar">{{ substr($lead->company_name, 0, 2) }}</div>
                @else
                    <div class="individual-avatar">
                        {{ substr($lead->first_name, 0, 1) }}{{ substr($lead->last_name, 0, 1) }}</div>
                @endif
            </div>
            <div>
                <h1 class="mb-1">
                    @if ($lead->type == 'Company')
                        {{ $lead->company_name }}
                    @else
                        {{ $lead->first_name }} {{ $lead->last_name }}
                    @endif
                </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $lead->priority }} Priority
                    </span>
                    <span class="badge bg-{{ $lead->stage->color }}">
                        {{ $lead->stage->name }}
                    </span>
                    @if ($lead->is_converted)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Converted
                        </span>
                    @else
                        <a class="btn btn-outline-primary"
                            href="{{ route(getPanelRoutes('leads.convert'), ['id' => $lead->id]) }}">Convert to
                            client</a>
                    @endif
                    <span class="lead-score" data-bs-toggle="tooltip" title="Lead Score">
                        {{ $lead->score ?? 0 }} <i class="fas fa-star text-warning"></i>
                    </span>

                    <p class="mt-2">
                       
                        @foreach ($lead->assignees as $user)
                            <a style="text-decoration: none;"
                                href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                                
                                <x-form-components.profile-avatar :title="$user->name"  :url="asset(
                                    'storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" :hw="35" />
                            </a>
                        @endforeach
                        <x-form-components.add-assignee
                        :title="'Add New'"
                         :action="route(getPanelRoutes('leads.addAssignee'))" :modal="$lead" :teamMates="$teamMates"
                            :hw="40" />
    
                    </p>
                </div>

               
             
            </div>
        </div>
    </div>

</div>
