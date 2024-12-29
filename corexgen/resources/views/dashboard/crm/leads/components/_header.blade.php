<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div class="lead-avatar">
                @if ($lead->type == 'Company')
                    <div class="company-avatar">{{ substr($lead->company_name, 0, 2) }}</div>
                @else
                    <div class="individual-avatar">
                        {{ substr($lead->first_name, 0, 1) }}{{ substr($lead->last_name, 0, 1) }}</div>
                @endif
            </div>
            <div>
                <h1 class="mb-1">
                    @if ($lead->type == 'Company')
                        {{ $lead->company_name }}
                    @else
                        {{ $lead->first_name }} {{ $lead->last_name }}
                    @endif
                </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $lead->priority }} Priority
                    </span>
                    <span class="badge bg-{{ $lead->stage->color }}">
                        {{ $lead->stage->name }}
                    </span>
                    @if ($lead->is_converted)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Converted
                        </span>
                    @endif
                    <span class="lead-score" data-bs-toggle="tooltip" title="Lead Score">
                        {{ $lead->score ?? 0 }} <i class="fas fa-star text-warning"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
 
</div>
