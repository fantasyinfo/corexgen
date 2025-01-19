<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Payments Lists</h5>
</div>

<div class="timeline-wrapper">
    @if ($company->paymentTransactions && $company->paymentTransactions->isNotEmpty())
        <div class="table-responsive ">
            <table class="table p-3  table-bordered ui daTableQuick">
                <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($company->paymentTransactions as $pt)
                        <tr>
                            <td>{{ $pt->id }}</td>
                            <td>{{$pt->payment_type}}</td>
                            <td>{{$pt->amount}}</td>
                            <td>{{$pt->currency}}</td>
                            <td><span class="badge bg-{{ CRM_STATUS_TYPES['PAYMENTSTRANSACTIONS']['BT_CLASSES'][$pt?->status]  }}">{{ $pt?->status }}</span></td>
                            <td>{{ formatDateTime($pt?->transaction_date) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-money"></i>
            </div>
            <h6>No Payments Yet</h6>
            <p class="text-muted">Payments will appear here, if any.</p>
        </div>
    @endif
</div>
