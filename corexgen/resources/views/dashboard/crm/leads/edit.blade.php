@extends('layout.app')

@section('content')
    @php
        // prePrintR($lead->toArray());
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="leadForm" action="{{ route(getPanelRoutes('leads.update')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $lead->id }}" />
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('leads.Update Lead') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('leads.Update Lead') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('leads.General Information') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leads"
                                        type="button" role="tab">
                                        {{ __('leads.Leads Information') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                        type="button" role="tab">
                                        {{ __('leads.Contact Details') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address"
                                        type="button" role="tab">
                                        {{ __('leads.Address') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="additional-tab" data-bs-toggle="tab"
                                        data-bs-target="#additional" type="button" role="tab">
                                        {{ __('leads.Additional Information') }}
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

                            <div class="tab-content mt-4" id="clientsTabsContent">
                                <!-- General Information Tab -->


                                <div class="tab-pane fade show active" id="general" role="tabpanel">


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="clientType" required>
                                                {{ __('leads.Type') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="type" id="clientType" required>
                                                <option value="Individual"
                                                    {{ $lead->type == 'Company' ? 'selected' : '' }}>Individual</option>
                                                <option value="Company" {{ $lead->type == 'Company' ? 'selected' : '' }}>
                                                    Company</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4" id="company_name_div">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="companyName" required>
                                                {{ __('leads.Company Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="company_name"
                                                id="companyName" placeholder="{{ __('Abc Pvt Ltd') }}"
                                                value="{{ old('company_name', $lead->company_name) }}" />
                                        </div>
                                    </div>



                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="firstName" required>
                                                {{ __('leads.First Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="first_name" id="firstName"
                                                placeholder="{{ __('First Name') }}"
                                                value="{{ old('first_name', $lead->first_name) }}" required />
                                        </div>
                                    </div>


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="lastName" required>
                                                {{ __('leads.Last Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="last_name" id="lastName"
                                                placeholder="{{ __('Last Name') }}"
                                                value="{{ old('last_name', $lead->last_name) }}" required />
                                        </div>
                                    </div>




                                    <hr>
                                    <p class="alert alert-secondary"><i class="fas fa-info-circle me-2 "></i>
                                        Please add / update <span class="text-success">Leads Details</span> on leads
                                        details tabs.</p>
                                </div>

                                <div class="tab-pane fade" id="leads" role="tabpanel">

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="title" required>
                                                {{ __('leads.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="title" id="title"
                                                placeholder="{{ __('New Development Project Lead') }}"
                                                value="{{ old('title', $lead->title) }}" required />
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="value">
                                                {{ __('leads.Value') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append prepend="$" append="USD"
                                                type="number" name="value" id="value"
                                                placeholder="{{ __('New Development Project Lead') }}"
                                                value="{{ old('value', $lead->value) }}" />
                                        </div>
                                    </div>



                                    <!-- priority -->
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="priority" required>
                                                {{ __('leads.Priority') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="priority" id="priority" required>
                                                @foreach (['Low', 'Medium', 'High'] as $pri)
                                                    <option value="{{ $pri }}"
                                                        {{ old('priority', $lead->priority) == $pri ? 'selected' : '' }}>
                                                        {{ $pri }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- groups -->
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="group_id">
                                                {{ __('leads.Groups') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="group_id" id="group_id">
                                                @foreach ($leadsGroups as $lg)
                                                    <option value="{{ $lg->id }}"
                                                        {{ old('group_id', $lead->group_id) == $lg->id ? 'selected' : '' }}>
                                                        {{ $lg->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- sources -->
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="source_id">
                                                {{ __('leads.Sources') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="source_id" id="source_id">
                                                @foreach ($leadsSources as $ls)
                                                    <option value="{{ $ls->id }}"
                                                        {{ old('source_id', $lead->source_id) == $ls->id ? 'selected' : '' }}>
                                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- stage -->
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="status_id" required>
                                                {{ __('leads.Stage') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="status_id" id="status_id" required>
                                                @foreach ($leadsStatus as $lst)
                                                    <option value="{{ $lst->id }}"
                                                        {{ old('status_id', $lead->status_id) == $lst->id ? 'selected' : '' }}>
                                                        <i class="fas fa-dot-circle"></i> {{ $lst->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>





                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="last_contacted_date">
                                                {{ __('leads.Last Contacted') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="last_contacted_date"
                                                id="last_contacted_date"
                                                value="{{ old('last_contacted_date', $lead->last_contacted_date) }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="last_activity_date">
                                                {{ __('leads.Last Activity') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="last_activity_date"
                                                id="last_activity_date"
                                                value="{{ old('last_activity_date', $lead->last_activity_date) }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="follow_up_date">
                                                {{ __('leads.Follow Up') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="follow_up_date"
                                                id="follow_up_date"
                                                value="{{ old('follow_up_date', $lead->follow_up_date) }}" />
                                        </div>
                                    </div>


                                    <!-- assign to  -->

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="assign_to[]">
                                                {{ __('leads.Assign To') }}
                                            </x-form-components.input-label>
                                        </div>

                                        <div class="col-lg-8">
                                            <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates"
                                                :name="'assign_to'" :multiple="true" :selected="$lead->assignees->pluck('id')->toArray()" />
                                        </div>
                                    </div>

                                    <!-- is Captured  -->

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="is_converted">
                                                {{ __('leads.Is Captured') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="is_converted"
                                                    id="isRequired_0"
                                                    {{ old('is_converted', $lead->is_converted) == 'on' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_converted">
                                                    {{ __('if checked, a client account will also created.') }}
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                    <hr>
                                    <x-form-components.tab-guidebox :nextTab="'Contact'" />

                                </div>
                                <!-- Contact Details Tab -->
                                <div class="tab-pane fade" id="contact" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="emails">
                                                {{ __('leads.Email') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="emailContainer">
                                                <div class="input-group mb-2">

                                                    <x-form-components.input-group type="email" name="email"
                                                        id="email" placeholder="{{ __('Email Address') }}"
                                                        value="{{ old('email', $lead->email) }}" required />
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="phones">
                                                {{ __('leads.Phone') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="phoneContainer">
                                                <div class="input-group mb-2">

                                                    <x-form-components.input-group type="tel" name="phone"
                                                        id="phone" placeholder="{{ __('Phone Number') }}"
                                                        value="{{ old('phone', $lead->phone) }}" required />
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- preferred_contact_method -->
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="preferred_contact_method" required>
                                                {{ __('leads.Prefferd Contact') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="preferred_contact_method"
                                                id="preferred_contact_method" required>
                                                @foreach (['Email', 'Phone', 'In-Person'] as $pcm)
                                                    <option value="{{ $pcm }}"
                                                        {{ old('preferred_contact_method', $lead->preferred_contact_method) == $pcm ? 'selected' : '' }}>
                                                        {{ $pcm }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <hr>
                                    <x-form-components.tab-guidebox :nextTab="'Address'" />
                                </div>


                                <!-- Addresses Tab -->
                                <div class="tab-pane fade" id="address" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressStreet"
                                                class="custom-class">
                                                {{ __('address.Address') }}
                                            </x-form-components.input-label>
                                        </div>

                                        <div class="col-lg-8">
                                            <x-form-components.textarea-group name="address.street_address"
                                                id="compnayAddressStreet" placeholder="Enter Registered Street Address"
                                                class="custom-class"
                                                value="{{ old('address.street_address', $lead?->address?->street_address) }}" />

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

                                                    @if ($countries)
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ old('address.country_id', $lead?->address?->country_id) == $country->id ? 'selected' : '' }}>
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
                                                value="{{ old('address.city_name', $lead?->address?->city?->name) }}"
                                                class="custom-class" />
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
                                                value="{{ old('address.pincode', $lead?->address?->postal_code) }}"
                                                class="custom-class" />

                                        </div>
                                    </div>
                                    <hr>
                                    <x-form-components.tab-guidebox :nextTab="'Additional'" />
                                </div>

                                <!-- Additional Information Tab -->
                                <div class="tab-pane fade" id="additional" role="tabpanel">

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="details">
                                                {{ __('leads.Additional Details') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <textarea name="details" id="details" class="form-control wysiwyg-editor" rows="5">{{ old('details') }}</textarea>
                                        </div>
                                    </div>
                                    @if (isset($customFields) && $customFields->isNotEmpty())
                                        <hr>
                                        <x-form-components.tab-guidebox :nextTab="'Custom Fields'" />
                                    @endif
                                </div>

                                <!-- Custom Fields Tab -->
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-create :customFields="$customFields" :cfOldValues="$cfOldValues" />
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
        $(document).ready(function() {
            $('#clientType').on('change', function() {
                var selectedType = $(this).val();
                if (selectedType === 'Company') {
                    $('#company_name_div').show();
                } else {
                    $('#company_name_div').hide();
                }
            });

            // Trigger change event on page load
            $('#clientType').trigger('change');
        });





        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for searchable dropdowns
            $('.searchSelectBox').select2({
                width: '100%',
                placeholder: 'Select an option'
            });



            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            // Initialize WYSIWYG editor
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.wysiwyg-editor',
                    height: 300,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! $lead->details !!}`);
                        });
                    },
                    menubar: false,
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        'autoresize',
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
            const form = document.getElementById('leadForm');
            form.querySelectorAll('input, select, textarea').forEach(field => {
                attachValidationListeners(field);
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Get active tab
                const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target')
                    .replace('#', '');

                // Update active tab input
                let activeTabInput = form.querySelector('input[name="active_tab"]');
                if (!activeTabInput) {
                    activeTabInput = document.createElement('input');
                    activeTabInput.type = 'hidden';
                    activeTabInput.name = 'active_tab';
                    form.appendChild(activeTabInput);
                }
                activeTabInput.value = activeTab;

                // Validate all fields
                let isValid = true;
                let firstInvalidField = null;

                form.querySelectorAll('input, select, textarea').forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                        }
                    }
                });

                if (!isValid && firstInvalidField) {
                    // Switch to tab containing first invalid field
                    const fieldTab = firstInvalidField.closest('.tab-pane').id;
                    const tabButton = document.querySelector(`[data-bs-target="#${fieldTab}"]`);
                    if (tabButton) {
                        const tab = new bootstrap.Tab(tabButton);
                        tab.show();
                    }

                    // Scroll to and focus the invalid field
                    firstInvalidField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstInvalidField.focus();
                    return;
                }

                if (isValid) {
                    // Sync WYSIWYG editor
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

                    this.submit();
                }
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
        });
    </script>
@endpush
