<td>{{ $item->title }}</td>
<td>{{ $item->project?->title }}</td>
<td>{{ $item->hourly_rate }}</td>
<td>{{ formatDateTime($item->due_date) }}</td>
<td>{{ $item->priority }}</td>
<td>
    {{ $item->stage?->name }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('tasks.view'), ['id' => $item->id]) }}" class="dt-link">
        View Task
    </a>
</td>
