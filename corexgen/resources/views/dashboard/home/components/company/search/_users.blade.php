<td>{{ $item->name }}</td>
<td>{{ $item->email }}</td>
<td>{{ $item->role?->role_name }}</td>
<td>
    {{ $item->status }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('users.view'), ['id' => $item->id]) }}" class="dt-link">
        View User
    </a>
</td>
