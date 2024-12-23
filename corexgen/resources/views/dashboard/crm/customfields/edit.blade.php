@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">
                    <form id="customFieldsForm" action="{{ route(getPanelRoutes('customfields.update')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type='hidden' name='id' value='{{ $customfield['id'] }}' />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('customfields.Update Custom Field') }}</span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('customfields.Save All Fields') }}
                                    </button>
                                </div>
                            </div>

                            <div id="customFieldsContainer">
                                <!-- Template for a single custom field -->
                                <div class="custom-field-group mb-4 border-bottom pb-4">
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="fieldLabel_0" required>
                                                {{ __('customfields.Field Label') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="fields[0][label]"
                                                id="fieldLabel_0" placeholder="{{ __('Ex: Employee Department') }}" required
                                                value="{{ old('fields.0.label', $customfield['fields'][0]['label']) }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="fieldType_0" required>
                                                {{ __('customfields.Field Type') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <select name="fields[0][type]" id="fieldType_0" required
                                                    class="form-control">

                                                    @foreach (CUSTOM_FIELDS_INPUT_TYPES as $key => $item)
                                                        <option value="{{ $key }}"
                                                            {{ old('fields.0.type', $customfield['fields'][0]['type']) == $key ? 'selected' : '' }}>
                                                            {{ __($item) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="entityType_0" required>
                                                {{ __('customfields.Apply To') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <select name="fields[0][entity_type]" id="entityType_0" required
                                                    class="form-control">
                                                    @foreach (CUSTOM_FIELDS_RELATION_TYPES['VALUES'] as $key => $item)
                                                        <option value="{{ $key }}"
                                                            {{ old('fields.0.entity_type', $customfield['fields'][0]['entity_type']) == $key ? 'selected' : '' }}>
                                                            {{ $item }}</option>
                                                    @endforeach
                                                </select>


                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center options-group" style="display: none;">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="fieldOptions_0">
                                                {{ __('customfields.Options') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.textarea-group name="fields[0][options]" id="fieldOptions_0"
                                                placeholder="{{ __('Enter options, one per line') }}" rows="4"
                                                value="{{ old('fields.0.options', $customfield['fields'][0]['options']) }}"
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label>
                                                {{ __('customfields.Settings') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    name="fields[0][is_required]" id="isRequired_0"
                                                    {{ old('fields.0.is_required', $customfield['fields'][0]['is_required']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isRequired_0">
                                                    {{ __('customfields.Required Field') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <button type="button" class="btn btn-outline-primary me-2" id="addMoreFields">
                                <i class="fas fa-plus"></i> {{ __('customfields.Add Another Field') }}
                            </button> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let fieldCounter = 1;

                const initialFieldType = document.querySelector('#fieldType_0');
                if (initialFieldType) {
                    toggleOptionsVisibility(initialFieldType);
                }

                // Function to show/hide options based on field type
                function toggleOptionsVisibility(selectElement) {
                    const optionsGroup = selectElement.closest('.custom-field-group').querySelector('.options-group');
                    optionsGroup.style.display = selectElement.value === 'select' ? 'flex' : 'none';
                }

                // Add event listener for existing field type selects
                document.querySelectorAll('[id^="fieldType_"]').forEach(select => {
                    select.addEventListener('change', (e) => toggleOptionsVisibility(e.target));
                });


            });
        </script>
    @endpush
@endsection
