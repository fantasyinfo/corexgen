<h6 class="detail-label">Sidebar</h6>

<div class="card  border-2 mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>

            <h6 class="stat-label">Activities</h6>
            <h3 class="stat-value">{{ count($activities) ?? 0 }}</h3>
        </div>
    </div>
</div>

@if (isset($customFields) && $customFields->isNotEmpty())

    <div class="card  border-2 mb-4">
        <div class="card-header">
            <h6 class="stat-label">Custom Fields</h6>
        </div>
        <div class="card-body  gap-2">

            @foreach ($customFields as $cf)
                @php
                    // Find the existing value for this custom field
                    $existingValue = $cfOldValues->firstWhere('definition_id', $cf['id']);
                    $fieldValue = $existingValue
                        ? $existingValue['field_value']
                        : old('custom_fields.' . $cf['id'], '');
                @endphp
                <div class="detail-group">
                    <label>{{ ucfirst($cf['field_label']) }}</label>
                    <p>{{ trim($fieldValue) != '' || null ? $fieldValue :  'NA'}}</p>
                </div>
            @endforeach
        </div>
    </div>

@endif
