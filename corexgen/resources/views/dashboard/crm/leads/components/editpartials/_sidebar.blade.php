<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <h6 class="stat-label">Deal Value</h6>
            <x-form-components.input-group-prepend-append prepend="$" append="USD"
            type="number" name="value" id="value"
            placeholder="{{ __('New Development Project Lead') }}"
            value="{{ old('value', $lead->value) }}" />
        </div>

    </div>
</div>


<div class="card   border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div>

            <h6 class="stat-label">Last Contact</h6>
            <h3 class="stat-value">
                <x-form-components.input-group type="date" name="last_contacted_date"
                id="last_contacted_date"
                value="{{ old('last_contacted_date', $lead->last_contacted_date) }}" />
            </h3>
        </div>
    </div>
</div>


<div class="card  border-0  mb-4">

    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div>
            <h6 class="stat-label">Follow Up</h6>
            <x-form-components.input-group type="date" name="follow_up_date"
                                                id="follow_up_date"
                                                value="{{ old('follow_up_date', $lead->follow_up_date) }}" />
            </h3>
        </div>
    </div>
</div>


<div class="card  border-0  mb-4">
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

    <div class="card  border-0  mb-4">
        <div class="card-header">
            <h6 class="stat-label">Custom Fields</h6>
        </div>
        <div class="card-body gap-2">

            @foreach ($customFields as $cf)
                @php
                    // Find the existing value for this custom field
                    $existingValue = $cfOldValues->firstWhere('definition_id', $cf['id']);
                    $fieldValue = $existingValue
                        ? $existingValue['field_value']
                        : old('custom_fields.' . $cf['id'], '');
                @endphp

                @Switch($cf['field_type'])
                    @case('text')
                    @case('number')

                    @case('date')
                    @case('time')
                        <div class="row mb-4" id="company_name_div">

                            <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                                {{ ucfirst($cf['field_label']) }}
                            </x-form-components.input-label>

                            <x-form-components.input-group type="{{ $cf['field_type'] }}"
                                name="custom_fields[{{ $cf['id'] }}]" id="{{ $cf['field_name'] }}"
                                placeholder="Please Enter {{ ucfirst($cf['field_label']) }}" value="{{ $fieldValue }}"
                                :required="$cf['is_required']" />

                        </div>
                    @break

                    @case('select')
                        <div class="row mb-4">

                            <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                                {{ ucfirst($cf['field_label']) }}
                            </x-form-components.input-label>

                            <select class="form-select" name="custom_fields[{{ $cf['id'] }}]" id="{{ $cf['field_name'] }}"
                                :required="$cf['is_required']">
                                @foreach ($cf['options'] as $options)
                                    <option value="{{ $options }}" {{ $fieldValue == $options ? 'selected' : '' }}>
                                        {{ $options }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    @break

                    @case('textarea')
                        <div class="row mb-4 align-items-center">

                            <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                                {{ ucfirst($cf['field_label']) }}
                            </x-form-components.input-label>

                            <x-form-components.textarea-group name="custom_fields[{{ $cf['id'] }}]"
                                id="{{ $cf['field_name'] }}" placeholder="Please Enter {{ ucfirst($cf['field_label']) }}"
                                :value="$fieldValue" class="custom-class" :required="$cf['is_required']" />

                        </div>
                    @break

                    @case('checkbox')
                        <div class="row mb-4 align-items-center">

                            <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                                {{ ucfirst($cf['field_label']) }}
                            </x-form-components.input-label>

                            <input type="checkbox" class="form-check-input" name="custom_fields[{{ $cf['id'] }}]"
                                id="{{ $cf['field_name'] }}" {{ $fieldValue == 'on' ? 'checked' : '' }}
                                :required="$cf['is_required']">

                        </div>
                    @break

                    @default
                @endswitch
            @endforeach
        </div>
    </div>

@endif
