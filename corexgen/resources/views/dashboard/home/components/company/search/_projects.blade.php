<td>{{ $item->title }}</td>
<td>{{ $item->client?->first_name }}</td>
<td>{{ $item->billing_type }}</td>
<td>{{ formatDateTime($item->start_date) }}</td>
<td>{{ formatDateTime($item->due_date)}}</td>
<td>
   {{ $item->status }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('projects.view'), ['id' => $item->id]) }}" class="dt-link">
        View Project
    </a>
</td>
