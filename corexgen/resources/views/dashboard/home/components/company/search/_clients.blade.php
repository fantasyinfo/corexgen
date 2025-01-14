<td>
    <strong>{{ $item->company_name }}</strong><br>
    <small>{{ $item->first_name }} {{ $item->last_name }}</small>
</td>
<td>
    {{ $item->primary_email }}<br>
    <small>{{ $item->primary_phone }}</small>
</td>
<td>
    {{ $item->status }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('clients.view'), ['id' => $item->id]) }}" class="dt-link">
        View Client
    </a>
</td>
