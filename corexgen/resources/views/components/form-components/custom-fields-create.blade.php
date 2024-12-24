<div class="tab-pane fade" id="custom-fields" role="tabpanel">

    @foreach ($customFields as $cf)
        @Switch($cf['field_type'])
            @case('text')
            @case('number')

            @case('date')
            @case('time')
                <div class="row mb-4" id="company_name_div">
                    <div class="col-lg-4">
                        <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                            {{ ucfirst($cf['field_label']) }}
                        </x-form-components.input-label>
                    </div>
                    <div class="col-lg-8">
                        <x-form-components.input-group type="{{ $cf['field_type'] }}" name="custom_fields[{{ $cf['id'] }}]"
                            id="{{ $cf['field_name'] }}" placeholder="Please Enter {{ ucfirst($cf['field_label']) }}"
                            value="{{ old('custom_fields.' . $cf['id'], '') }}" :required="$cf['is_required']" />
                    </div>
                </div>
            @break

            @case('select')
                <div class="row mb-4">
                    <div class="col-lg-4">
                        <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                            {{ ucfirst($cf['field_label']) }}
                        </x-form-components.input-label>
                    </div>
                    <div class="col-lg-8">
                        <select class="form-select" name="custom_fields[{{ $cf['id'] }}]" id="{{ $cf['field_name'] }}"
                            :required="$cf['is_required']">
                            @foreach ($cf['options'] as $options)
                                <option value="{{ $options }}"
                                    {{ old('custom_fields.' . $cf['id']) == $options ? 'selected' : '' }}>
                                    {{ $options }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @break

            @case('textarea')
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                            {{ ucfirst($cf['field_label']) }}
                        </x-form-components.input-label>
                    </div>
                    <div class="col-lg-8">
                        <x-form-components.textarea-group name="custom_fields[{{ $cf['id'] }}]"
                            id="{{ $cf['field_name'] }}" placeholder="Please Enter {{ ucfirst($cf['field_label']) }}"
                            value="{{ old('custom_fields.' . $cf['id'], '') }}" class="custom-class" :required="$cf['is_required']" />
                    </div>
                </div>
            @break

            @case('checkbox')
                <div class="row mb-4 align-items-center">
                    <div class="col-lg-4">
                        <x-form-components.input-label for="{{ $cf['field_name'] }}" :required="$cf['is_required']">
                            {{ ucfirst($cf['field_label']) }}
                        </x-form-components.input-label>
                    </div>
                    <div class="col-lg-8">
                        <input type="checkbox" class="form-check-input" name="custom_fields[{{ $cf['id'] }}]"
                            id="{{ $cf['field_name'] }}" :required="$cf['is_required']">


                    </div>
                </div>
            @break

            @default
        @endswitch
    @endforeach

</div>
