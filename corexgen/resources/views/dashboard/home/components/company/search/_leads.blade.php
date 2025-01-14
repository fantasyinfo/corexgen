<td>{{ $item->title }} {{ $item->first_name }} {{ $item->last_name }}
</td>
<td>{{ $item->company_name }}</td>
<td>
    {{ $item->email }}<br>
    <small>{{ $item->phone }}</small>
</td>
<td>
   {{ $item->stage?->name }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('leads.view'), ['id' => $item->id]) }}" class="dt-link">
        View Lead
    </a>
</td>
