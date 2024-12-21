{{-- resources/views/components/data-table.blade.php --}}
<div class="table-responsive  table-bg">
    <table id="{{ $id }}" class="table table-striped table-bordered ui celled">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableConfig = new DataTableConfig('#{{ $id }}', {
            ajaxUrl: '{{ $ajaxUrl }}',
            columns: @json($columns),
            @if($bulkDeleteUrl)
            bulkDeleteUrl: '{{ $bulkDeleteUrl }}',
            @endif
            @if($csrfToken)
            csrfToken: '{{ $csrfToken }}',
            @endif
        });
        
        const table = tableConfig.init();
        
        // Refresh table when filters change
        $('[data-filter]').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush