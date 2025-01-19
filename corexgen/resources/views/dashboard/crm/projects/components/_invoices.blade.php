<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Invoice Lists</h5>

        <a href="{{ route(getPanelRoutes('invoices.create')) }}?type=project&id={{ $project->id }}&refrer=projects.view"
            class="btn btn-primary">  <i class="fas fa-plus"></i> Create Invoice</a>

</div>
<div class="timeline-wrapper">
    @if ($invoices && $invoices->isNotEmpty())
        <div class="table-responsive ">
            <table class="table p-3  table-bordered ui daTableQuick">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Issue Date</th>
                        <th>Due Until</th>
                        <th>Actions</th>
                    </tr>
                </thead>    
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{!! "<a class='dt-link' href='" . route(getPanelRoutes('invoices.view'), $invoice->id) . "' target='_blank'> $invoice->_prefix  $invoice->_id </a>" !!}
                            </td>
                            <td>
                                @php
                                    $title = $invoice->task?->title;
                                @endphp
                                {!! "<a class='dt-link' href='" . route(getPanelRoutes('tasks.view'), $invoice->task?->id) . "' target='_blank'>$title  </a>" !!}</td>
                            <td>
                                <span class="badge bg-{{ CRM_STATUS_TYPES['INVOICES']['STATUS'][$invoice->status] }}">
                                    {{ ucfirst(strtolower($invoice->status)) }}
                                </span>
                            </td>
                            <td>{{ formatDateTime($invoice->issue_date) }}</td>
                            <td>{{ $invoice->due_date ? formatDateTime($invoice->due_date) : 'Not Specified' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route(getPanelRoutes('invoices.sendInvoice'), $invoice->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-envelope me-2"></i> Send to client</a>
                                    
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
                <i class="fas fa-receipt"></i>
            </div>
            <h6>No Invoice Yet</h6>
            <p class="text-muted">Invoice will appear here, if any.</p>
        </div>
    @endif
</div>
