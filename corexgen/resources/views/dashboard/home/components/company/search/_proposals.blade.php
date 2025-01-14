<td>{{ $item->_prefix . '-' . $item->_id }}</td>
<td>{{ $item->title }}</td>
<td>{{ $item?->typable?->first_name }}</td>
<td>{{ formatDateTime($item->creating_date) }}</td>
<td>{{ formatDateTime($item->valid_date) }}</td>
<td>{{ $item->status }}</td>
<td> <a href="{{ route(getPanelRoutes('proposals.view'), ['id' => $item->id]) }}" class="dt-link">
        View Proposal
    </a> </td>
