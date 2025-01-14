<td>{{ $item->role_name }}</td>
<td>
    <a href="{{ route(getPanelRoutes('role.edit'), ['id' => $item->id]) }}" class="dt-link">
        Edit Role
    </a>
</td>
