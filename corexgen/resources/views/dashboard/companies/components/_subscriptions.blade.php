<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Subscriptions Lists</h5>
</div>

<div class="timeline-wrapper">
    @if ($company?->subscriptions && $company?->subscriptions->isNotEmpty())
        <div class="table-responsive table-bg">
            <table class="table p-3  table-bordered ui celled">
                <thead>
                    <tr>
                        <th>Subscription ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Next Billing Date</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($company?->subscriptions as $sb)
                        <tr>
                            <td>{{ $sb->id }}</td>
                            <td>{{formatDateTime($sb?->start_date)}}</td>
                            <td>{{formatDateTime($sb?->end_date)}}</td>
                            <td>{{formatDateTime($sb?->next_billing_date)}}</td>
                            <td><span class="badge bg-{{ CRM_STATUS_TYPES['SUBSCRIPTION']['BT_CLASSES'][$sb?->status] }}">{{ $sb?->status }}</span></td>
                            <td>{{ formatDateTime($sb?->created_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-seedling"></i>
            </div>
            <h6>No Subscriptions Yet</h6>
            <p class="text-muted">Subscriptions will appear here, if any.</p>
        </div>
    @endif
</div>
