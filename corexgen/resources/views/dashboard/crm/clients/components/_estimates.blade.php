<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Estimates Lists</h5>
    <a href="{{ route(getPanelRoutes('estimates.create')) }}?type=client&id={{$client->id}}&refrer=clients.view" class="btn btn-primary">Create Estimate</a>
</div>

<div class="timeline-wrapper">
    @if ($estimates && $estimates->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                 
                        <th>Estimate ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Valid Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estimates as $estimate)
                        <tr>
                        
                            <td>{{ $estimate->_prefix }}{{ $estimate->_id }}</td>
                            <td>{!! "<a class='dt-link' href='" . route(getPanelRoutes('estimates.view'), $estimate->id) . "' target='_blank'>$estimate->title</a>" !!}</td>
                            <td>
                                <span class="badge bg-{{ CRM_STATUS_TYPES['ESTIMATES']['STATUS'][$estimate->status] }}">
                                    {{ ucfirst(strtolower($estimate->status)) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($estimate->creating_date)->format('F d, Y') }}</td>
                            <td>{{ $estimate->valid_date ? \Carbon\Carbon::parse($estimate->valid_date)->format('F d, Y') : 'Not Specified' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route(getPanelRoutes('estimates.sendEstimate'), $estimate->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope me-2"></i> Send to client</a>
                                    
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
                <i class="fas fa-file-signature"></i>
            </div>
            <h6>No Estimates Yet</h6>
            <p class="text-muted">Proposals will appear here, if any.</p>
        </div>
    @endif
</div>
