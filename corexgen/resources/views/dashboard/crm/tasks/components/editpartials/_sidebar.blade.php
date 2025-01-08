<h6 class="detail-label">Sidebar (Edit)</h6>

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
