<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Proposals Lists</h5>
    <a href="{{ route(getPanelRoutes('proposals.create')) }}?type=lead&id={{$lead->id}}&refrer=leads.view" class="btn btn-primary">Create Proposal</a>
</div>

<div class="timeline-wrapper">
    @if ($proposals && $proposals->isNotEmpty())
        <div class="table-responsive ">
            <table class="table p-3  table-bordered ui daTableQuick">
                <thead>
                    <tr>
                 
                        <th>Proposal ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Valid Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proposals as $proposal)
                        <tr>
                        
                            <td>{{ $proposal->_prefix }}{{ $proposal->_id }}</td>
                            <td>{!! "<a class='dt-link' href='" . route(getPanelRoutes('proposals.view'), $proposal->id) . "' target='_blank'>$proposal->title</a>" !!}</td>
                            <td>
                                <span class="badge bg-{{ CRM_STATUS_TYPES['PROPOSALS']['STATUS'][$proposal->status] }}">
                                    {{ ucfirst(strtolower($proposal->status)) }}
                                </span>
                            </td>
                            <td>{{ formatDateTime($proposal->creating_date) }}</td>
                            <td>{{ $proposal->valid_date ? formatDateTime($proposal->valid_date) : 'Not Specified' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route(getPanelRoutes('proposals.sendProposal'), $proposal->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope me-2"></i> Send to client</a>
                                    
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-flag"></i>
            </div>
            <h6>No Proposals Yet</h6>
            <p class="text-muted">Proposals will appear here, if any.</p>
        </div>
    @endif
</div>
