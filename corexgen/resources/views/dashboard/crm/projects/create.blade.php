@extends('layout.app')

@section('content')
    @php
        //prePrintR($customFields->toArray());
    @endphp
    @push('style')
        <style>
            .tox-promotion {
                height: none !important;
            }

            .progress-value {
                background: #007bff;
                color: white;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 14px;
                min-width: 48px;
                text-align: center;
                font-weight: 500;
            }

            /* Option 2: Bordered design */
            .progress-value {
                border: 2px solid #007bff;
                color: #007bff;
                padding: 3px 10px;
                border-radius: 6px;
                font-size: 14px;
                min-width: 48px;
                text-align: center;
                font-weight: 500;
            }

            /* Option 3: Material design style */
            .progress-value {
                background: #e3f2fd;
                color: #1976d2;
                padding: 4px 12px;
                border-radius: 4px;
                font-size: 14px;
                min-width: 48px;
                text-align: center;
                font-weight: 500;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .error-badge {
                font-size: 0.75rem;
                padding: 0.25em 0.6em;
                border-radius: 50%;
            }

            .validation-errors-list {
                padding-left: 1.25rem;
                margin-bottom: 0;
            }

            .validation-errors-list li {
                margin-bottom: 0.5rem;
            }

            .validation-errors-list li:last-child {
                margin-bottom: 0;
            }

            .nav-link.text-danger {
                position: relative;
            }
        </style>
    @endpush
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="projectForm" action="{{ route(getPanelRoutes('projects.store')) }}" method="POST" novalidate>
                        @csrf
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('projects.Create New Project') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('projects.Create Project') }}</span>
                                </button>
                            </div>

                            <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('projects.General Information') }}
                                    </button>
                                </li>

                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="custom-fields-tab" data-bs-toggle="tab"
                                            data-bs-target="#custom-fields" type="button" role="tab">
                                            {{ __('customfields.Custom Fields') }}
                                        </button>
                                    </li>
                                @endif
                            </ul>


                            <div class="tab-content mt-4" id="projectsTabs">
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="projectTitle" class="custom-class" required>
                                                {{ __('projects.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="title" id="projectTitle"
                                                placeholder="{{ __('Web Development in Laravel') }}"
                                                value="{{ old('title') }}" required class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-3 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="client_id" required>
                                                {{ __('projects.Select Client') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select name="client_id" id="client_id" class="form-select searchSelectBox">
                                                @foreach ($clients as $item)
                                                    @php
                                                        $nameAndEmail = $item->first_name . ' ' . $item->last_name;
                                                        if ($item->type == 'Company') {
                                                            $nameAndEmail = $item->company_name;
                                                        }
                                                        $nameAndEmail .= !$item->primary_email
                                                            ? ' [No Email Found...] '
                                                            : " [ $item->primary_email ]";
                                                    @endphp
                                                    <option value="{{ $item->id }}"
                                                        {{ old('client_id') == $item->id ? 'selected' : '' }}>
                                                        {{ $nameAndEmail }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center" id="billing_type">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="billingType" class="custom-class" required>
                                                {{ __('projects.Billing Type') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8 d-flex align-items-center">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="radio" value="Hourly"
                                                    name="billing_type" id="Hourly" checked>
                                                <label class="form-check-label" for="Hourly">
                                                    Hourly
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="billing_type"
                                                    value="One-Time" id="One-Time">
                                                <label class="form-check-label" for="One-Time">
                                                    One-Time
                                                </label>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mb-4 align-items-center" id="per_hour_cost">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="perHourCost" class="custom-class" required>
                                                {{ __('projects.Per Hour Cost') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append step="0.001"
                                                prepend="{{ getSettingValue('Currency Symbol') }}"
                                                append="{{ getSettingValue('Currency Code') }}" type="number"
                                                name="per_hour_cost" id="perHourCost" placeholder="{{ __('99') }}"
                                                value="{{ old('per_hour_cost') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center" id="one_time_cost" style="display:none;">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="perHourCost" class="custom-class" required>
                                                {{ __('projects.One Time Cost') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append step="0.001"
                                                prepend="{{ getSettingValue('Currency Symbol') }}"
                                                append="{{ getSettingValue('Currency Code') }}" type="number"
                                                name="one_time_cost" id="perHourCost" placeholder="{{ __('99') }}"
                                                value="{{ old('one_time_cost') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="startDate" class="custom-class" required>
                                                {{ __('projects.Start Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" placeholder="Select Date"
                                                name="start_date" id="startDate" value="{{ old('start_date') }}"
                                                required class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="dueDate" class="custom-class">
                                                {{ __('projects.Due Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" placeholder="Select Date"
                                                name="due_date" id="dueDate" value="{{ old('due_date') }}"
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="deadLine" class="custom-class">
                                                {{ __('projects.Deadline') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" placeholder="Select Date"
                                                name="deadline" id="deadLine" value="{{ old('deadline') }}"
                                                class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="progressInput" class="custom-class">
                                                {{ __('projects.Progress') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <div class="col-md-10">
                                                    <input type="range" name="progress" class="form-range"
                                                        id="progressInput" name="progress" min="0" max="100"
                                                        value="10">
                                                </div>
                                                <div class="col-md-2">
                                                    <span id="progressValue" class="ms-2 progress-value">10%</span>
                                                </div>
                                            </div>


                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="estimatedHours" class="custom-class">
                                                {{ __('projects.Estimated Hours') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="number" name="estimated_hours"
                                                id="estimatedHours" placeholder="{{ __('10') }}"
                                                value="{{ old('estimated_hours') }}" class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="timeSpent" class="custom-class">
                                                {{ __('projects.Time Spent') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="number" name="time_spent"
                                                id="timeSpent" placeholder="{{ __('2') }}"
                                                value="{{ old('time_spent', 0) }}" class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="assign_to[]">
                                                {{ __('projects.Members') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">

                                            <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates"
                                                :name="'assign_to'" :multiple="true" :selected="old('assign_to')" />


                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">


                                        <x-form-components.input-label for="desc" class="custom-class">
                                            {{ __('projects.Description') }}
                                        </x-form-components.input-label>

                                        <x-form-components.textarea-group name="description" id="desc"
                                            placeholder="Describe the project details" value="{{ old('description') }}"
                                            class="custom-class description" />

                                    </div>
                                    @if (isset($customFields) && $customFields->isNotEmpty())
                                        <hr>
                                        <x-form-components.tab-guidebox :nextTab="'Custom Fields'" />
                                    @endif
                                </div>
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-create :customFields="$customFields" />
                                @endif
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            // Initialize WYSIWYG editor
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.description',
                    height: 400,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    width: '100%',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! old('description', '') !!}`);
                        });
                    },
                    menubar: false,
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        // 'autoresize',
                        'autosave',
                        'charmap',
                        'code',
                        'codesample',
                        'directionality',
                        'emoticons',
                        'fullscreen',
                        'help',
                        'lists',
                        'link',
                        'image',


                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',


                        'insertdatetime',
                        'media',
                        'table',



                        'wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                                                                                                                                      alignleft aligncenter alignright alignjustify | \
                                                                                                                                                      bullist numlist outdent indent | removeformat | help | \
                                                                                                                                                      link image media preview codesample table'
                });
            }

            // Get references to the elements
            const $hourlyRadio = $("#Hourly");
            const $oneTimeRadio = $("#One-Time");
            const $perHourCostRow = $("#per_hour_cost");
            const $oneTimeCostRow = $("#one_time_cost");

            // Function to toggle input visibility
            function toggleBillingType() {
                if ($hourlyRadio.is(":checked")) {
                    $perHourCostRow.show(); // Show hourly cost input
                    $oneTimeCostRow.hide(); // Hide one-time cost input
                } else if ($oneTimeRadio.is(":checked")) {
                    $perHourCostRow.hide(); // Hide hourly cost input
                    $oneTimeCostRow.show(); // Show one-time cost input
                }
            }

            // Add event listeners to the radio buttons
            $hourlyRadio.change(toggleBillingType);
            $oneTimeRadio.change(toggleBillingType);

            // Initial toggle on page load
            toggleBillingType();

            document.getElementById('progressInput').addEventListener('input', function() {
                document.getElementById('progressValue').textContent = this.value + '%';
            });


            // new items

            const form = document.getElementById('projectForm');

            // Real-time validation function
            function validateField(field) {
                const isValid = field.checkValidity();
                field.classList.toggle('is-invalid', !isValid);
                field.classList.toggle('is-valid', isValid);

                // Remove existing feedback
                const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }

                if (!isValid) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';

                    if (field.validity.valueMissing) {
                        feedback.textContent = 'This field is required';
                    } else if (field.validity.typeMismatch) {
                        if (field.type === 'email') {
                            feedback.textContent = 'Please enter a valid email address';
                        } else if (field.type === 'tel') {
                            feedback.textContent = 'Please enter a valid phone number';
                        }
                    } else if (field.validity.patternMismatch) {
                        feedback.textContent = field.title || 'Please match the requested format';
                    }

                    field.parentNode.appendChild(feedback);
                }

                return isValid;
            }

            if (!document.getElementById('validationErrorsContainer')) {
                const errorContainer = document.createElement('div');
                errorContainer.id = 'validationErrorsContainer';
                errorContainer.className = 'mb-4';
                errorContainer.style.display = 'none';
                errorContainer.innerHTML = `
        <div class="alert alert-danger">
            <h6 class="alert-heading mb-2">Please correct the following errors:</h6>
            <ul class="validation-errors-list mb-0"></ul>
        </div>`;

                // Insert it before the tabs
                const tabs = document.getElementById('clientsTabs');
                tabs.parentNode.insertBefore(errorContainer, tabs);
            }

            form.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', function() {
                    if (this.value.trim() !== '') {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        const errorDiv = this.nextElementSibling;
                        if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                            errorDiv.remove();
                        }
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Clear previous error states
                document.querySelectorAll('.nav-link').forEach(tab => {
                    tab.classList.remove('text-danger');
                    const badge = tab.querySelector('.error-badge');
                    if (badge) badge.remove();
                });

                const errorContainer = document.getElementById('validationErrorsContainer');
                const errorsList = errorContainer.querySelector('.validation-errors-list');
                errorsList.innerHTML = '';
                errorContainer.style.display = 'none';

                // Validate all fields
                let isValid = true;
                let tabErrors = new Map();
                let errorMessages = [];

                // Validate each tab
                document.querySelectorAll('.tab-pane').forEach(tabPane => {
                    const tabId = tabPane.id;
                    const tabButton = document.querySelector(`[data-bs-target="#${tabId}"]`);
                    const tabName = tabButton.textContent.trim();
                    let tabErrorCount = 0;

                    // Check all required fields in this tab
                    tabPane.querySelectorAll('[required]').forEach(field => {
                        const isFieldValid = field.value.trim() !== '';
                        if (!isFieldValid) {
                            isValid = false;
                            tabErrorCount++;

                            // Get field label
                            let fieldLabel = '';
                            const labelElement = document.querySelector(
                                `label[for="${field.id}"]`);
                            if (labelElement) {
                                fieldLabel = labelElement.textContent.replace('*', '')
                                    .trim();
                            } else {
                                fieldLabel = field.placeholder || field.name;
                            }

                            // Add to error messages
                            errorMessages.push({
                                tab: tabName,
                                field: fieldLabel
                            });

                            // Add invalid class to field
                            field.classList.add('is-invalid');

                            // Add error message below field if not exists
                            let errorDiv = field.nextElementSibling;
                            if (!errorDiv || !errorDiv.classList.contains(
                                    'invalid-feedback')) {
                                errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback';
                                errorDiv.textContent = 'This field is required';
                                field.parentNode.insertBefore(errorDiv, field.nextSibling);
                            }
                        } else {
                            // Remove invalid state if field is valid
                            field.classList.remove('is-invalid');
                            field.classList.add('is-valid');
                            const errorDiv = field.nextElementSibling;
                            if (errorDiv && errorDiv.classList.contains(
                                    'invalid-feedback')) {
                                errorDiv.remove();
                            }
                        }
                    });

                    if (tabErrorCount > 0) {
                        tabErrors.set(tabId, tabErrorCount);
                    }
                });

                if (!isValid) {
                    // Show error container
                    errorContainer.style.display = 'block';

                    // Group errors by tab
                    const groupedErrors = errorMessages.reduce((acc, error) => {
                        if (!acc[error.tab]) {
                            acc[error.tab] = [];
                        }
                        acc[error.tab].push(error.field);
                        return acc;
                    }, {});

                    // Create error messages
                    Object.entries(groupedErrors).forEach(([tab, fields]) => {
                        const li = document.createElement('li');
                        li.innerHTML =
                            `<strong>${tab}:</strong> Required fields missing: ${fields.join(', ')}`;
                        errorsList.appendChild(li);
                    });

                    // Add error indicators to tabs
                    tabErrors.forEach((errorCount, tabId) => {
                        const tabButton = document.querySelector(`[data-bs-target="#${tabId}"]`);
                        if (tabButton) {
                            tabButton.classList.add('text-danger');

                            const badge = document.createElement('span');
                            badge.className = 'badge bg-danger ms-2 error-badge';
                            badge.textContent = errorCount;
                            tabButton.appendChild(badge);
                        }
                    });

                    // Scroll to error container
                    errorContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });


                    return false;
                }

                // If form is valid, proceed with submission
                if (isValid) {
                    // Sync WYSIWYG editor if exists
                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }

                    // Store form data backup
                    const formData = new FormData(form);
                    const formDataObj = {};
                    formData.forEach((value, key) => {
                        formDataObj[key] = value;
                    });
                    localStorage.setItem('formBackup', JSON.stringify(formDataObj));

                    // Submit the form
                    form.submit();
                }
            });

        });
    </script>
@endpush
