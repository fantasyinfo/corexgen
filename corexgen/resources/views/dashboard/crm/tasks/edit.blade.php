@extends('layout.app')

@section('content')
    @php
        //prePrintR($customFields->toArray());
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
    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">
                    <form id="taskForm" action="{{ route(getPanelRoutes('tasks.update')) }}" method="POST"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $task->id }}" />
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('tasks.Update Task') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button form="taskForm" type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('tasks.Update Task') }}</span>
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
                                            <x-form-components.input-label for="billable">
                                                {{ __('tasks.Check') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="checkbox" name="billable" id="billable" value="1"
                                                {{ old('billable', $task->billable) == '1' ? 'checked' : '' }}
                                                class="custom-class form-check-input" />
                                            <label class="me-2">Billable</label>
                                            <br>
                                            @error('billable')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror

                                            <input type="checkbox" name="visible_to_client" id="visible_to_client"
                                                value="1"
                                                {{ old('visible_to_client', $task->visible_to_client) == '1' ? 'checked' : '' }}
                                                class="custom-class form-check-input" />
                                            <label class="me-2">Visible to client</label>
                                            <br>
                                            @error('visible_to_client')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="title" required>
                                                {{ __('tasks.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="title" id="title"
                                                placeholder="{{ __('Enter Title') }}"
                                                value="{{ old('title', $task->title) }}" required class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="hourly_rate">
                                                {{ __('tasks.Hourly Rate') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append type="number"
                                                class="custom-class" id="hourly_rate" step="0.001"
                                                prepend="{{ getSettingValue('Currency Symbol') }}"
                                                append="{{ getSettingValue('Currency Code') }}" name="hourly_rate"
                                                placeholder="{{ __('99999') }}"
                                                value="{{ old('hourly_rate', $task->hourly_rate) }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="start_date">
                                                {{ __('tasks.Start Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" placeholder="Select Date"
                                                name="start_date" id="start_date"
                                                value="{{ old('start_date', $task->start_date) }}" class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="due_date">
                                                {{ __('tasks.Due Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" placeholder="Select Date"
                                                name="due_date" id="due_date"
                                                value="{{ old('due_date', $task->due_date) }}" class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="milestone_id">
                                                {{ __('tasks.Milestone') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select searchSelectBox" name="milestone_id"
                                                id="milestone_id">
                                                <option>Select Milestone (optional)</option>
                                                @foreach ($milestones as $ml)
                                                    <option value="{{ $ml->id }}"
                                                        {{ old('milestone_id', $task->milestone_id) == $ml->id ? 'selected' : '' }}>
                                                        {{ $ml->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('milestone_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="priority" required>
                                                {{ __('tasks.Priority') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="priority" id="priority" required>
                                                @foreach (['Low', 'Medium', 'High', 'Urgent'] as $pri)
                                                    <option value="{{ $pri }}"
                                                        {{ old('priority', $task->priority) == $pri ? 'selected' : '' }}>
                                                        {{ $pri }}</option>
                                                @endforeach
                                            </select>
                                            @error('priority')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="status_id" required>
                                                {{ __('tasks.Status') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="status_id" id="status_id" required>
                                                @foreach ($tasksStatus as $ts)
                                                    <option value="{{ $ts->id }}"
                                                        {{ old('status_id', $task->status_id) == $ts->id ? 'selected' : '' }}>
                                                        {{ $ts->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('status_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="related_to" required>
                                                {{ __('tasks.Related To') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="related_to" id="related_to" required>
                                                @foreach (TASKS_RELATED_TO['STATUS'] as $key => $pri)
                                                    <option value="{{ $key }}"
                                                        {{ old('related_to', $task->related_to) == $key ? 'selected' : '' }}>
                                                        {{ $pri }}</option>
                                                @endforeach
                                            </select>
                                            @error('related_to')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="project_id">
                                                {{ __('tasks.Project') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select searchSelectBox" name="project_id"
                                                id="project_id">
                                                @foreach ($projects as $pro)
                                                    <option value="{{ $pro->id }}"
                                                        {{ old('project_id', $task->project_id) == $pro->id ? 'selected' : '' }}>
                                                        {{ $pro->title }}</option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>




                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="assign_to[]">
                                                {{ __('tasks.Assign To') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates"
                                                :name="'assign_to'" :multiple="true" :selected="old('assign_to', $task->assignees->pluck('id')->toArray())" />
                                            @error('assign_to')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="files[]">
                                                {{ __('tasks.Attach Files') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <input class="form-control" type="file" name="files[]" multiple />
                                            @error('files')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            @if ($errors->has('files.*'))
                                                @foreach ($errors->get('files.*') as $fileErrors)
                                                    @foreach ($fileErrors as $fileError)
                                                        <span class="text-danger d-block">{{ $fileError }}</span>
                                                    @endforeach
                                                @endforeach
                                            @endif



                                        </div>


                                        <!-- ... file upload input ... -->
                                        <div class="my-4">
                                            @if ($task->attachments->count() > 0)
                                                @foreach ($task->attachments as $attachment)
                                                    <div class="attachment-item mb-4">
                                                        <div class="d-flex justify-content-start gap-4 align-items-center">
                                                            <!-- Attachment Icon -->
                                                            <div class="attachment-icon">
                                                                @if (in_array($attachment->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                    <img height="100" width="100%"
                                                                        src="{{ $attachment->file_path }}"
                                                                        alt="{{ $attachment->file_name }}"
                                                                        class="attachment-preview" />
                                                                @else
                                                                    <i class="fas fa-file"></i>
                                                                @endif
                                                            </div>

                                                            <!-- Attachment Content -->
                                                            <div class="attachment-content ms-3">
                                                                <div
                                                                    class="attachment-header text-muted d-flex gap-1 justify-content-start align-items-center">
                                                                    <h6 class="attachment-name mb-0">
                                                                        {{ truncateFileName($attachment->file_name) }}
                                                                        <small class="text-muted ms-2">
                                                                            ({{ number_format($attachment->size / 1024, 2) }}
                                                                            KB)
                                                                        </small>
                                                                    </h6>
                                                                    <div class="attachment-actions">
                                                                        <!-- View and Download buttons remain the same -->



                                                                        <a title="View Attachment" data-toggle="tooltip"
                                                                            href="{{ $attachment->file_path }}"
                                                                            class="btn btn-outline-secondary btn-sm"
                                                                            target="_blank">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>

                                                                        <!-- Download Button -->
                                                                        <a title="Download Attachment"
                                                                            data-toggle="tooltip"
                                                                            href="{{ $attachment->file_path }}"
                                                                            class="btn btn-outline-secondary btn-sm"
                                                                            download>
                                                                            <i class="fas fa-download"></i>
                                                                        </a>
                                                                        <!-- Delete Button -->
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-sm confirm-delete-attachment"
                                                                            data-attachment-id="{{ $attachment->id }}"
                                                                            data-delete-url="{{ route(getPanelRoutes('tasks.attachment.destroy'), ['id' => $attachment->id]) }}">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                    </div>


                                    <div class="row mb-4">

                                        <x-form-components.input-label for="description">
                                            {{ __('tasks.Description') }}
                                        </x-form-components.input-label>

                                        <textarea name="description" id="description" class="form-control wysiwyg-editor" rows="5">{{ old('description', $task->description) }}</textarea>

                                    </div>
                                    <hr>
                                    @if (isset($customFields) && $customFields->isNotEmpty())
                                        <hr>
                                        <x-form-components.tab-guidebox :nextTab="'Custom Fields'" />
                                    @endif
                                </div>

                                <!-- Custom Fields Tab -->
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-edit :customFields="$customFields" :cfOldValues="$cfOldValues" />
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
                    selector: '.wysiwyg-editor',
                    height: 400,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! old('description', $task->description) !!}`);
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


            document.querySelectorAll('.confirm-delete-attachment').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this attachment?')) {
                        const deleteUrl = this.getAttribute('data-delete-url');

                        // Create and submit a form dynamically
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = deleteUrl;

                        // Add CSRF token
                        const csrfToken = "{{ csrf_token() }}";
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;

                        // Add method override
                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'DELETE';

                        form.appendChild(csrfInput);
                        form.appendChild(methodInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // new items

            const form = document.getElementById('taskForm');

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
