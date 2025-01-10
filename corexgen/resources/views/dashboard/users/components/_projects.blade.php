<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Projects Lists</h5>
</div>

<div class="timeline-wrapper">
    @if ($projects && $projects->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Title</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{!! "<a class='dt-link' href='" .
                                route(getPanelRoutes('projects.view'), $project->id) .
                                "' target='_blank'>$project->title</a>" !!}</td>
                            <td><span class="badge bg-{{ CRM_STATUS_TYPES['PROJECTS']['BT_CLASSES'][$project?->status] }}">{{ $project?->status }}</span></td>
             
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-folder-plus"></i>
            </div>
            <h6>No Projects Yet</h6>
            <p class="text-muted">Projects will appear here, if any.</p>
        </div>
    @endif
</div>
