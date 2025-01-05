<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Contracts Lists</h5>
    <a href="{{ route(getPanelRoutes('contracts.create')) }}?type=lead&id={{$lead->id}}&refrer=leads.view" class="btn btn-primary">Create Contract</a>
</div>

<div class="timeline-wrapper">
    @if ($contracts && $contracts->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                 
                        <th>Contract ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Valid Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contracts as $contract)
                        <tr>
                        
                            <td>{{ $contract->_prefix }}{{ $contract->_id }}</td>
                            <td>{!! "<a class='dt-link' href='" . route(getPanelRoutes('contracts.view'), $contract->id) . "' target='_blank'>$contract->title</a>" !!}</td>
                            <td>
                                <span class="badge bg-{{ CRM_STATUS_TYPES['CONTRACTS']['STATUS'][$contract->status] }}">
                                    {{ ucfirst(strtolower($contract->status)) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($contract->creating_date)->format('F d, Y') }}</td>
                            <td>{{ $contract->valid_date ? \Carbon\Carbon::parse($contract->valid_date)->format('F d, Y') : 'Not Specified' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route(getPanelRoutes('contracts.sendContract'), $contract->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope me-2"></i> Send to client</a>
                                    
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
                <i class="fas fa-file-contract"></i>
            </div>
            <h6>No Contracts Yet</h6>
            <p class="text-muted">Contracts will appear here, if any.</p>
        </div>
    @endif
</div>
