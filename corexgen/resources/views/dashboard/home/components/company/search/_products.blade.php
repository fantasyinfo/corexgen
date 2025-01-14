<td>
    <strong>{{ $item->title }}</strong><br>
</td>
<td>
    {{ $item->type }}<br>
</td>
<td>
   {{ $item->rate }}
</td>
<td>
   {{ $item->unit }}
</td>
<td>
    <a href="{{ route(getPanelRoutes('products_services.view'), ['id' => $item->id]) }}" class="dt-link">
        View Product
    </a>
</td>
