@extends('layout.app')

@section('content')
    @push('style')
        <style>
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


                    <form id="userForm" action="{{ route(getPanelRoutes('users.store')) }}" method="POST" novalidate>
                        @csrf
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('users.Create New User') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('users.Create User') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('users.General') }}
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address"
                                        type="button" role="tab">
                                        {{ __('users.Address') }}
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

                            <div class="tab-content mt-4" id="companyTabsContent">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <!-- Full Name Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="nameName" class="custom-class" required>
                                                {{ __('users.Full Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" class="custom-class"
                                                id="nameName" name="name" placeholder="{{ __('John Doe') }}"
                                                value="{{ old('name') }}" required />

                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="emailName" class="custom-class" required>
                                                {{ __('users.Email') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="email" class="custom-class"
                                                id="emailName" name="email" placeholder="{{ __('john@email.com') }}"
                                                value="{{ old('email') }}" required />

                                        </div>
                                    </div>

                                    <!-- Role Selection Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="role_id" class="custom-class" required>
                                                {{ __('users.Select Role') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('role_id') is-invalid @enderror"
                                                    name="role_id" id="role_id">
                                                    @if ($roles && $roles->isNotEmpty())
                                                        @foreach ($roles as $role)
                                                            <option value="{{ $role->id }}"
                                                                {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                                {{ $role->role_name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No roles available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="role_idError">
                                                    @error('role_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Password Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="passName" class="custom-class" required>
                                                {{ __('users.Password') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="password" class="custom-class"
                                                id="passName" name="password" placeholder="{{ __('********') }}"
                                                value="{{ old('password') }}" required />

                                        </div>
                                    </div>
                                    <hr>
                                    <x-form-components.tab-guidebox :nextTab="'Address'" />
                                </div>
                                <!-- Addresses Tab -->
                                <div class="tab-pane fade" id="address" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressStreet" class="custom-class">
                                                {{ __('address.Address') }}
                                            </x-form-components.input-label>
                                        </div>

                                        <div class="col-lg-8">
                                            <x-form-components.textarea-group name="address.street_address"
                                                id="compnayAddressStreet" placeholder="Enter Registered Street Address"
                                                class="custom-class" value="{{ old('address.street_address') }}" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressCountry"
                                                class="custom-class">
                                                {{ __('address.Country') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('address.country_id') is-invalid @enderror"
                                                    name="address.country_id" id="country_id">
                                                    <option value="" selected> ----- Select Country ----------
                                                    </option>
                                                    @if ($country)
                                                        @foreach ($country as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ old('address.country_id') == $country->id ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No country available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="country_idError">
                                                    @error('address.country_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressCity" class="custom-class">
                                                {{ __('address.City') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="address.city_name"
                                                id="compnayAddressCity" placeholder="{{ __('Enter City') }}"
                                                value="{{ old('address.city_name') }}" class="custom-class" />
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressPincode"
                                                class="custom-class">
                                                {{ __('address.Pincode') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="address.pincode"
                                                id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                                value="{{ old('address.pincode') }}" class="custom-class" />

                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Fields Tab -->
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add custom tab validation or handling
            const tabs = document.querySelectorAll('#companyTabs .nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // You can add custom logic here if needed
                    console.log(`Switched to tab: ${this.textContent}`);
                });
            });


            // new items

            const form = document.getElementById('userForm');

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
