<td>{{ $item->title }}</td>
<td>{{ $item->event_type }}</td>
<td><a href="{{ $item->meeting_link }}" class="dt-link">Meeting Link</a></td>
<td>{{ formatDateTime($item->start_date) }}</td>
<td>{{ $item->priority }}</td>
