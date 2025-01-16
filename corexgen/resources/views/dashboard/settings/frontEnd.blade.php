@extends('layout.app')
@section('content')
    @php

        $heroSection = $settings->where('key', 'hero')->first()->value;
        $featuresSection = $settings->where('key', 'features')->first()->value;
        $solutionsSection = $settings->where('key', 'solutions')->first()->value;
        $pricingSection = $settings->where('key', 'plans')->first()->value;
        $testimonialsSection = $settings->where('key', 'testimonials')->first()->value;
        $footerSection = $settings->where('key', 'footer')->first()->value;
        // prePrintR($testimonialsSection);
    @endphp
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
    <div class="container-fluid">
        <div class="row">

            <div class="card stretch stretch-full">
                <form id="frontEndForm" action="{{ route(getPanelRoutes('settings.frontendUpdate')) }}" method="POST"
                    novalidate>
                    @csrf
                    @method('PUT')
                    <div class="card-body">

                        <div class="mb-4 d-flex align-items-center justify-content-between">
                            <p class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('frontend.Front End Settings') }}</span>
                                <span
                                    class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span>{{ __('frontend.Save Settings') }}</span>
                            </button>
                        </div>
                        <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="hero-tab" data-bs-toggle="tab" data-bs-target="#hero"
                                    type="button" role="tab">
                                    {{ __('frontend.Hero Section') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features"
                                    type="button" role="tab">
                                    {{ __('frontend.Features Section') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="solutions-tab" data-bs-toggle="tab" data-bs-target="#solutions"
                                    type="button" role="tab">
                                    {{ __('frontend.Solutions Section') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing"
                                    type="button" role="tab">
                                    {{ __('frontend.Pricing Section') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="testimonials-tab" data-bs-toggle="tab"
                                    data-bs-target="#testimonials" type="button" role="tab">
                                    {{ __('frontend.Testimonials Section') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer"
                                    type="button" role="tab">
                                    {{ __('frontend.Footer Section') }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-4" id="clientsTabsContent">
                            <div class="tab-pane fade show active" id="hero" role="tabpanel">
                                @include('dashboard.settings.components._hero')
                            </div>
                            <div class="tab-pane fade" id="features" role="tabpanel">
                                @include('dashboard.settings.components._features')
                            </div>
                            <div class="tab-pane fade" id="solutions" role="tabpanel">
                                @include('dashboard.settings.components._solutions')
                            </div>
                            <div class="tab-pane fade" id="pricing" role="tabpanel">
                                @include('dashboard.settings.components._pricing')
                            </div>
                            <div class="tab-pane fade" id="testimonials" role="tabpanel">
                                @include('dashboard.settings.components._testimonials')
                            </div>
                            <div class="tab-pane fade" id="footer" role="tabpanel">
                                @include('dashboard.settings.components._footer')
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection


@push('scripts')
    <script>
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

        // Attach validation listeners to a field
        function attachValidationListeners(field) {
            field.addEventListener('blur', () => {
                validateField(field);
            });

            let timeout;
            field.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    validateField(field);
                }, 500);
            });
        }

        // Initialize validation for all form fields
        const form = document.getElementById('frontEndForm');
        form.querySelectorAll('input, select, textarea').forEach(field => {
            attachValidationListeners(field);
        });



        // Initialize tab from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('active_tab') || 'general';
        const tabToShow = document.querySelector(`button[data-bs-target="#${activeTab}"]`);
        if (tabToShow) {
            const tab = new bootstrap.Tab(tabToShow);
            tab.show();
        }

        // Restore form data from localStorage if it exists
        const savedFormData = localStorage.getItem('formBackup');
        if (savedFormData) {
            try {
                const formDataObj = JSON.parse(savedFormData);
                Object.entries(formDataObj).forEach(([key, value]) => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field) {
                        field.value = value;
                        // Trigger change event for Select2 fields
                        if ($(field).hasClass('select2-hidden-accessible')) {
                            $(field).trigger('change');
                        }
                    }
                });
                // Clear the backup after restoration
                localStorage.removeItem('formBackup');
            } catch (error) {
                console.error('Error restoring form data:', error);
                localStorage.removeItem('formBackup');
            }
        }

        // new items

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
    </script>
@endpush
