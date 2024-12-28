@extends('layout.app')

@section('content')
    @php
        // prePrintR($activities->toArray());
    @endphp
    <div class="container-fluid py-4">
        <!-- Lead Header -->
        <div class="card mb-4 border-0 lead-header-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="lead-avatar">
                                @if ($lead->type == 'Company')
                                    <div class="company-avatar">{{ substr($lead->company_name, 0, 2) }}</div>
                                @else
                                    <div class="individual-avatar">
                                        {{ substr($lead->first_name, 0, 1) }}{{ substr($lead->last_name, 0, 1) }}</div>
                                @endif
                            </div>
                            <div>
                                <h1 class="mb-1">
                                    @if ($lead->type == 'Company')
                                        {{ $lead->company_name }}
                                    @else
                                        {{ $lead->first_name }} {{ $lead->last_name }}
                                    @endif
                                </h1>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-info">
                                        <i class="fas fa-flag me-1"></i> {{ $lead->priority }} Priority
                                    </span>
                                    <span class="badge bg-{{ $lead->stage->color }}">
                                        {{ $lead->stage->name }}
                                    </span>
                                    @if ($lead->is_converted)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Converted
                                        </span>
                                    @endif
                                    <span class="lead-score" data-bs-toggle="tooltip" title="Lead Score">
                                        {{ $lead->score ?? 0 }} <i class="fas fa-star text-warning"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end gap-2 mt-3 mt-lg-0">

                            @if (!empty($lead->phone))
                                <a href="tel:{{ $lead->phone }}" class="btn btn-primary">
                                    <i class="fas fa-phone-alt me-2"></i> Call
                                </a>
                            @endif
                            @if (!empty($lead->email))
                                <a href="mailto:{{ $lead->email }}" class="btn btn-secondary">
                                    <i class="fas fa-envelope me-2"></i> Email
                                </a>
                            @endif

                            <a class="btn btn-warning" href="{{ route(getPanelRoutes($module) . '.edit', $lead->id) }}"
                                data-toggle="tooltip" title="Edit">
                                <i class="fas fa-pencil-alt me-2"></i>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                    <div class="col-md-6">
                                        <h6 class="detail-label">Basic Information</h6>
                                        <div class="detail-group">
                                            <label>Type</label>
                                            <p>{{ $lead->type }}</p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Title</label>
                                            <p>{{ $lead->title }}</p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Email</label>
                                            <p><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Phone</label>
                                            <p><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="detail-label">Additional Information</h6>
                                        <div class="detail-group">
                                            <label>Source</label>
                                            <p>{{ $lead->source->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Group</label>
                                            <p>{{ $lead->group->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Preferred Contact</label>
                                            <p>{{ $lead->preferred_contact_method }}</p>
                                        </div>
                                        <div class="detail-group">
                                            <label>Assigned To</label>
                                            <p>
                                                @foreach ($lead->assignees as $user)
                                                    <a
                                                        href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                                                        <x-form-components.profile-avatar :hw="40"
                                                            :url="asset(
                                                                'storage/' .
                                                                    ($user->profile_photo_path ??
                                                                        'avatars/default.webp'),
                                                            )" :title="$user->name" />
                                                    </a>
                                                @endforeach
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h6 class="detail-label">Details</h6>
                                    <p class="lead-details">{!! $lead->details !!}</p>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="activities">
                                @include('dashboard.crm.leads.components._activity')
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


                <div class="card border-0  mb-4">
                    <div class="card-body">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h6 class="stat-label">Deal Value</h6>
                        <h3 class="stat-value">${{ number_format($lead->value, 2) }}</h3>
                    </div>
                </div>


                <div class="card border-0  mb-4">
                    <div class="card-body">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h6 class="stat-label">Last Contact</h6>
                        <h3 class="stat-value">
                            {{ $lead->last_contacted_date ? $lead->last_contacted_date->diffForHumans() : 'Never' }}
                        </h3>
                    </div>
                </div>


                <div class="card border-0  mb-4">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="stat-label">Follow Up</h6>
                        <h3 class="stat-value">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : 'Not Set' }}</h3>
                    </div>
                </div>


                <div class="card border-0  mb-4">
                    <div class="card-body">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h6 class="stat-label">Activities</h6>
                        <h3 class="stat-value">{{ count($activities) ?? 0 }}</h3>
                    </div>
                </div>


                <!-- Next Actions -->
                <div class="card border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Next Actions</h5>
                            <button class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Task
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="task-list">
                            <!-- Task items would go here -->
                        </div>
                    </div>
                </div>

                <!-- Related Contacts -->
                <div class="card border-0 mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">Related Contacts</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="related-contacts-list">
                            <!-- Contact items would go here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
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
            background: var(--border-color);
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
            background: var(--body-bg);
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
            padding: 0.75rem;
        }

        .change-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
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
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .activity-changes {
            background: rgba(0, 0, 0, 0.2);
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
