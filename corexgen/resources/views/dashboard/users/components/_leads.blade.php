<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Leads Lists</h5>
</div>

<div class="timeline-wrapper">
    @if ($leads && $leads->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leads as $lead)
                        <tr>
                            <td>{{ $lead->id }}</td>
                            <td>{!! "<a class='dt-link' href='" .
                                route(getPanelRoutes('leads.view'), $lead->id) .
                                "' target='_blank'>$lead->title</a>" !!}</td>
                            <td><span class="badge bg-{{ $lead?->stage?->color }}">{{ $lead?->stage?->name }}</span></td>
                            <td>{{ $lead?->assignedBy?->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-phone-volume"></i>
            </div>
            <h6>No Leads Yet</h6>
            <p class="text-muted">Leads will appear here, if any.</p>
        </div>
    @endif
</div>
