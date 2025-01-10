<h6 class="detail-label">Sidebar</h6>


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
