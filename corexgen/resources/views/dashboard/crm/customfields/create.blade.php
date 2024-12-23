@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">
                    <form id="customFieldsForm" action="{{ route(getPanelRoutes('customfields.store')) }}" method="POST">
                        @csrf
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('customfields.Create New Custom Field') }}</span>
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
                                                id="fieldLabel_0" placeholder="{{ __('Ex: Employee Department') }}"
                                                required />
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
                                                    class="form-control ">
                                                    <option value="text">{{ __('Text') }}</option>
                                                    <option value="number">{{ __('Number') }}</option>
                                                    <option value="select">{{ __('Dropdown') }}</option>
                                                    <option value="date">{{ __('Date') }}</option>
                                                    <option value="textarea">{{ __('Text Area') }}</option>
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
                                                    class="form-control  ">
                                                    <option value="client">{{ __('Client') }}</option>
                                                    <option value="user"> {{ __('Users & Employees') }}</option>
                                                    <option value="role"> {{ __('Roles') }}</option>
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
                                                placeholder="{{ __('Enter options, one per line') }}" rows="4" />
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
                                                    name="fields[0][is_required]" id="isRequired_0">
                                                <label class="form-check-label" for="isRequired_0">
                                                    {{ __('customfields.Required Field') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary me-2" id="addMoreFields">
                                <i class="fas fa-plus"></i> {{ __('customfields.Add Another Field') }}
                            </button>
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

                // Function to show/hide options based on field type
                function toggleOptionsVisibility(selectElement) {
                    const optionsGroup = selectElement.closest('.custom-field-group').querySelector('.options-group');
                    optionsGroup.style.display = selectElement.value === 'select' ? 'flex' : 'none';
                }

                // Add event listener for existing field type selects
                document.querySelectorAll('[id^="fieldType_"]').forEach(select => {
                    select.addEventListener('change', (e) => toggleOptionsVisibility(e.target));
                });

                // Add new field group
                document.getElementById('addMoreFields').addEventListener('click', function() {
                    const container = document.getElementById('customFieldsContainer');
                    const template = container.querySelector('.custom-field-group').cloneNode(true);

                    // Update IDs and names
                    template.querySelectorAll('[id]').forEach(element => {
                        element.id = element.id.replace('_0', `_${fieldCounter}`);
                    });
                    template.querySelectorAll('[name]').forEach(element => {
                        element.name = element.name.replace('[0]', `[${fieldCounter}]`);
                    });

                    // Clear values
                    template.querySelectorAll('input[type="text"], textarea').forEach(element => {
                        element.value = '';
                    });
                    template.querySelectorAll('select').forEach(element => {
                        element.selectedIndex = 0;
                    });
                    template.querySelectorAll('input[type="checkbox"]').forEach(element => {
                        element.checked = false;
                    });

                    // Add remove button for additional fields
                    if (fieldCounter > 0) {
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'btn btn-outline-danger btn-sm mt-2';
                        removeBtn.innerHTML = '<i class="fas fa-trash"></i> Remove Field';
                        removeBtn.onclick = function() {
                            this.closest('.custom-field-group').remove();
                        };
                        template.appendChild(removeBtn);
                    }

                    // Add new event listeners
                    template.querySelectorAll('[id^="fieldType_"]').forEach(select => {
                        select.addEventListener('change', (e) => toggleOptionsVisibility(e.target));
                    });

                    container.appendChild(template);
                    fieldCounter++;
                });
            });
        </script>
    @endpush
@endsection
