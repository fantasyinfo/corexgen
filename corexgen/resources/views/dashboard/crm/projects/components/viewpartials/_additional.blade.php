<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>
    <div class="detail-group">
        <label class="stat-label">{{ $project?->type === 'Hourly' ? 'Per hour cost' : 'One time cost' }}</label>
        <p class="stat-value">{{ getSettingValue('Currency Symbol') }}
            {{ $project?->type === 'Hourly' ? $project?->per_hour_cost : $project?->one_time_cost }}</p>
    </div>

    <div class="detail-group">
        <label class="stat-label">Start Date</label>
        <p class="stat-value">
            {{ $project->start_date ? formatDateTime($project->start_date) : 'Never' }}
        </p>
    </div>

    <div class="detail-group">
        <label class="stat-label">Due Date</label>
        <p class="stat-value">{{ $project?->due_date ? formatDateTime($project?->due_date) : 'Not Set' }}
        </p>
    </div>

    <div class="detail-group">
        <label class="stat-label">Deadline</label>
        <p class="stat-value">{{ $project?->deadline ? formatDateTime($project?->deadline) : 'Not Set' }}
        </p>
    </div>

    <div class="detail-group">
        <label class="stat-label">Activities</label>
        <p class="stat-value">{{ count($activities) ?? 0 }}</p>
    </div>

    @if (isset($customFields) && $customFields->isNotEmpty())
        <label class="stat-label">Custom Fields</label>
        <div class="detail-group">
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
                    <p>{{ trim($fieldValue) != '' || null ? $fieldValue : 'NA' }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
