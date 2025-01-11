<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Project</th>
                        <th>Task</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentInvoices ?? [] as $invoice)
                        <tr>
                            <td>{{ $invoice->_id }}</td>
                            <td>{{ $invoice->client->first_name }}</td>
                            <td>{{ $invoice?->project?->title }}</td>
                            <td>{{ $invoice?->task?->title }}</td>
                            <td>{{getSettingValue('Currency Symbol')}} {{ number_format($invoice->total_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $invoice->status_color }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>