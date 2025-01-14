<td>{{ $item->_prefix . '-' . $item->_id }}</td>
<td>{{ $item?->client?->first_name }}</td>
<td>{{ number_format($item->total_amount) }}</td>
<td>{{ formatDateTime($item->issue_date) }}</td>
<td>{{ formatDateTime($item->due_date) }}</td>
<td>{{ $item->status }}</td>
<td> <a href="{{ route(getPanelRoutes('invoices.view'), ['id' => $item->id]) }}" class="dt-link">
        View Invoice
    </a> </td>
