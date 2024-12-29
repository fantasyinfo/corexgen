@extends('layout.app')

@section('content')
    @php
        // prePrintR($customFields->toArray());
    @endphp






    <div class="container-fluid ">
        <!-- Lead Header -->
        <div class="card mb-4 border-0 lead-header-card">
            <div class="card-body">
                @include('dashboard.crm.leads.components.viewpartials._header')
            </div>
        </div>

        <div id="viewDiv">
            <div class="row">
                <!-- Main Content Column -->
                <div class="col-lg-8">

                    <!-- Lead Details Tabs -->
                    <div class="card border-0 mb-4">
                        <div class="card-header bg-transparent border-bottom-0">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#details">
                                        <i class="fas fa-info-circle me-2"></i>Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#activities">
                                        <i class="fas fa-history me-2"></i>Activities
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#notes">
                                        <i class="fas fa-sticky-note me-2"></i>Notes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#files">
                                        <i class="fas fa-paperclip me-2"></i>Files
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Details Tab -->
                                <div class="tab-pane fade show active" id="details">
                                    <div class="row g-4">
                                        @include('dashboard.crm.leads.components.viewpartials._basic')
                                        @include('dashboard.crm.leads.components.viewpartials._additional')
                                    </div>
                                    <div class="mt-4">
                                        <h6 class="detail-label">Address</h6>
                                        <p class="lead-details">
                                            {{ $lead?->address?->street_address }},
                                            {{ $lead?->address?->city?->name }},
                                            {{ $lead?->address?->country?->name }},
                                            {{ $lead?->address?->postal_code }}
                                        </p>
                                    </div>
                                    <div class="mt-4">
                                        <h6 class="detail-label">Details</h6>
                                        <p class="lead-details">{!! $lead->details !!}</p>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="activities">
                                    @include('dashboard.crm.leads.components.viewpartials._activity')
                                </div>

                                <!-- Notes Tab -->
                                <div class="tab-pane fade" id="notes">
                                    <div class="notes-section">
                                        <div class="mb-3">
                                            <textarea class="form-control" rows="3" placeholder="Add a note..."></textarea>
                                            <button class="btn btn-primary mt-2">Add Note</button>
                                        </div>
                                        <div class="note-list">
                                            <!-- Note items would go here -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Files Tab -->
                                <div class="tab-pane fade" id="files">
                                    <div class="files-section">
                                        <div class="file-upload-area mb-3">
                                            <input type="file" class="d-none" id="fileUpload">
                                            <label for="fileUpload" class="file-upload-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Drop files here or click to upload</span>
                                            </label>
                                        </div>
                                        <div class="file-list">
                                            <!-- File items would go here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->

                    @include('dashboard.crm.leads.components.viewpartials._sidebar')

                </div>
            </div>
        </div>


        <div id="editDiv">
            <form id="leadEditForm" method="POST" action="{{ route(getPanelRoutes('leads.update')) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{$lead->id}}" />
                <input type="hidden" name="from_kanban" value="true" />
                <div class="row">
                    <!-- Main Content Column -->
                    <div class="col-lg-8">

                        <!-- Lead Details Tabs -->
                        <div class="card border-0 mb-4">
                            <div class="card-header bg-transparent border-bottom-0">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#edit-details">
                                            <i class="fas fa-info-circle me-2"></i>Details
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#edit-activities">
                                            <i class="fas fa-history me-2"></i>Activities
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#edit-notes">
                                            <i class="fas fa-sticky-note me-2"></i>Notes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#edit-files">
                                            <i class="fas fa-paperclip me-2"></i>Files
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- Details Tab -->
                                    <div class="tab-pane fade show active" id="edit-details">
                                        <div class="row g-4">
                                            @include('dashboard.crm.leads.components.editpartials._basic')
                                            @include('dashboard.crm.leads.components.editpartials._additional')
                                        </div>
                                        <div class="mt-4">
                                            <h6 class="detail-label">Address</h6>
                                            <p class="lead-details">
                                            <p class="my-1">
                                                <x-form-components.textarea-group name="address.street_address"
                                                    id="compnayAddressStreet"
                                                    placeholder="Enter Registered Street Address" class="custom-class"
                                                    value="{{ old('address.street_address', $lead?->address?->street_address) }}" />
                                            </p>
                                            <h6 class="detail-label">Country</h6>
                                            <p class="my-1">
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
                                            </p>
                                            <h6 class="detail-label mt-4">City</h6>
                                            <p class="my-1">
                                                <x-form-components.input-group type="text" name="address.city_name"
                                                    id="compnayAddressCity" placeholder="{{ __('Enter City') }}"
                                                    value="{{ old('address.city_name', $lead?->address?->city?->name) }}"
                                                    class="custom-class" />
                                            </p>
                                            <h6 class="detail-label">Pincode</h6>
                                            <p class="my-1">
                                                <x-form-components.input-group type="text" name="address.pincode"
                                                    id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                                    value="{{ old('address.pincode', $lead?->address?->postal_code) }}"
                                                    class="custom-class" />
                                            </p>
                                            </p>
                                        </div>
                                        <div class="mt-4">
                                            <x-form-components.input-label for="details">
                                                {{ __('leads.Additional Details') }}
                                            </x-form-components.input-label>
                                            <textarea name="details" id="details" class="form-control wysiwyg-editor" rows="5">{{ old('details') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="edit-activities">
                                        @include('dashboard.crm.leads.components.editpartials._activity')
                                    </div>

                                    <!-- Notes Tab -->
                                    <div class="tab-pane fade" id="edit-notes">
                                        <div class="notes-section">
                                            <div class="mb-3">
                                                <textarea class="form-control" rows="3" placeholder="Add a note..."></textarea>
                                                <button class="btn btn-primary mt-2">Add Note</button>
                                            </div>
                                            <div class="note-list">
                                                <!-- Note items would go here -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Files Tab -->
                                    <div class="tab-pane fade" id="edit-files">
                                        <div class="files-section">
                                            <div class="file-upload-area mb-3">
                                                <input type="file" class="d-none" id="fileUpload">
                                                <label for="fileUpload" class="file-upload-label">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                    <span>Drop files here or click to upload</span>
                                                </label>
                                            </div>
                                            <div class="file-list">
                                                <!-- File items would go here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Quick Stats -->

                        @include('dashboard.crm.leads.components.editpartials._sidebar')

                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> <span>{{ __('leads.Update Lead') }}</span>
                </button>
            </form>
        </div>

    </div>
@endsection

@push('style')
    <style>
        #viewDiv {
            display: block;
        }

        #editDiv {
            display: none;
        }

        .card {
            background-color: var(--card-bg);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Lead Header Styles */
        /* Lead Header Styles */
        .lead-header-card {
            background: var(--card-bg);
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .lead-avatar {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
        }

        .company-avatar {
            background: var(--primary-color);
            color: white;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }

        .individual-avatar {
            background: var(--secondary-color);
            color: white;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }

        .lead-score {
            background: var(--light-color);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 1rem;
            background: var(--card-bg);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Tabs Styling */
        .nav-tabs {
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            border: none;
            color: var(--neutral-gray);
            padding: 1rem 1.5rem;
            font-weight: 500;
            position: relative;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: transparent;
            border: none;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }

        /* Detail Sections */
        .detail-label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .detail-group {
            margin-bottom: 1rem;
        }

        .detail-group label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .detail-group p {
            margin: 0;
            font-weight: 500;
        }

        .timeline-wrapper {
            position: relative;
            padding: 1rem 0;
        }

        .timeline {
            position: relative;
            padding-left: 3rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 0;
            height: 100%;
            width: 2px;
            background: var(--body-bg);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        .timeline-icon {
            position: absolute;
            left: -3rem;
            width: 30px;
            height: 30px;
        }

        .icon-wrapper {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        .timeline-content-wrapper {
            /* background: var(--body-bg); */
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }



        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .activity-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .activity-title h6 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--neutral-gray);
        }

        .activity-changes {
            background: var(--input-bg);
            border-radius: 0.5rem;
            /* padding: 0.75rem; */
        }

        .change-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* padding: 0.5rem 0; */
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border-color);
        }

        .change-item:last-child {
            border-bottom: none;
        }

        .field-label {
            font-weight: 500;
            color: var(--body-color);
        }

        .change-values {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }

        .old-value {
            color: var(--danger-color);
            text-decoration: line-through;
        }

        .new-value {
            color: var(--success-color);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            font-size: 2.5rem;
            color: var(--neutral-gray);
            margin-bottom: 1rem;
        }

        /* Dark mode adjustments */
        [data-bs-theme="dark"] .timeline-content-wrapper {
            /* background: var(--card-bg); */
            border: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .activity-changes {
            /* background: rgba(0, 0, 0, 0.2); */
        }

        /* File Upload Styling */
        .file-upload-label {
            border: 2px dashed var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .file-upload-label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Task List Styling */
        .task-list {
            padding: 1rem;
        }

        .task-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: var(--input-bg);
            margin-bottom: 0.5rem;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
        }

        .bg-priority-high {
            background-color: var(--danger-color);
            color: white;
        }

        .bg-priority-medium {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        .bg-priority-low {
            background-color: var(--success-color);
            color: white;
        }

        /* Button Styling */
        .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        /* Dark Mode Specific Styles */
        [data-bs-theme="dark"] .card {
            background-color: var(--card-bg);
        }

        [data-bs-theme="dark"] .nav-tabs .nav-link {
            color: var(--neutral-gray);
        }

        [data-bs-theme="dark"] .nav-tabs .nav-link.active {
            color: var(--primary-color);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .lead-header-card .d-flex {
                flex-direction: column;
            }

            .stat-card {
                margin-bottom: 1rem;
            }

            .nav-tabs .nav-link {
                padding: 0.75rem 1rem;
            }
        }

        /* Animation Effects */
        .card,
        .btn,
        .badge {
            transition: all 0.3s ease;
        }

        /* Custom Scrollbar */
        /* ::-webkit-scrollbar {
                                                            width: 8px;
                                                            height: 8px;
                                                        }

                                                        ::-webkit-scrollbar-track {
                                                            background: var(--body-bg);
                                                        }

                                                        ::-webkit-scrollbar-thumb {
                                                            background: var(--neutral-gray);
                                                            border-radius: 4px;
                                                        }

                                                        ::-webkit-scrollbar-thumb:hover {
                                                            background: var(--primary-color);
                                                        } */
    </style>
@endpush


@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        $("#editToggle").click(function(e) {
            e.preventDefault();
            $("#viewDiv").toggle();
            $("#editDiv").toggle();
        })

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
    </script>
@endpush
