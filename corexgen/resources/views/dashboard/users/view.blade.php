@extends('layout.app')
@push('style')
    <style>
        #editDetails,
        #updateBtn {
            display: none;
        }

        .divider {
            background-color: var(--body-bg);
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

        .divider-container {
            display: flex;
            justify-content: center;
            align-items: stretch;
        }

        .divider {
            width: 1px;
            background-color: var(--body-bg);
            /* Adjust color as per your theme */
            height: 100%;
        }

        .main-content-view {
            padding-right: 1rem;
            /* Add space near the divider */
        }

        .sidebar-view {
            padding-left: 1rem;
            /* Add space near the divider */
        }
    </style>
@endpush


@section('content')
  
    <div class="container-fluid ">
        <!-- Lead Header -->
        <div class="card mb-4 border-0 pb-0 lead-header-card">
            <div class="card-body">
                @include('dashboard.users.components._header')
            </div>
        </div>


        <div class="row">
            <!-- Main Content Column -->

            <!-- Lead Details Tabs -->
            <div class="card border-0 ">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#details" role="tab">
                                <i class="fas fa-info-circle me-2"></i>Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#leads">
                                <i class="fas fa-phone-volume me-2"></i>Leads
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tasks">
                                <i class="fas fa-tasks me-2"></i>Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#projects">
                                <i class="fas fa-file me-2"></i>Projects
                            </a>
                        </li>
                        
                    </ul>
                </div>
                <div class="card-body mt-0 pt-0">
                    <div class="tab-content">
                        <!-- Details View Tab -->
                        <div class="tab-pane fade show active" id="details">
                            @if (isset($permissions['UPDATE']) && hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']))
                                <div class="d-flex my-3 justify-content-lg-end gap-2 ">
                                    <button id='editToggle' title="Edit" data-toggle="tooltip"
                                        class="btn btn-outline-secondary">
                                        <i class="fas fa-pencil-alt me-2"></i>
                                    </button>
                                    <button form="leadEditForm" title="Update" data-toggle="tooltip" type="submit"
                                        id="updateBtn" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> <span>Update </span>
                                    </button>
                                </div>
                            @endif
                            <!-- View Details -->
                            <div class="row g-4" id="viewDetails">
                                <div class="col-lg-7 main-content-view">
                                    <div class="row">
                                        @include('dashboard.users.components.viewpartials._basic')
                                    
                                    </div>
                                    @include('dashboard.users.components.viewpartials._address')
                         
                                </div>
                                <div class="col-lg-1 divider-container">
                                    <div class="divider"></div>
                                </div>
                                <div class="col-lg-4 sidebar-view">
                                    @include('dashboard.users.components.viewpartials._sidebar')
                                </div>
                            </div>
                            <!-- Edit Details -->
                            <div class="row g-4" id="editDetails">
                                <form id="leadEditForm" method="POST" action="{{ route(getPanelRoutes('users.update')) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="id" value="{{ $user->id }}" />
                                    <input type="hidden" name="email" value="{{ $user->email }}" />
                                    <input type="hidden" name="from_view" value="true" />
                                    <div class="row">
                                        <div class="col-lg-7 main-content-view">
                                            <div class="row">
                                                @include('dashboard.users.components.editpartials._basic')
                                           
                                            </div>
                                            @include('dashboard.users.components.editpartials._address')
                                         
                                        </div>
                                        <div class="col-lg-1 divider-container">
                                            <div class="divider"></div>
                                        </div>
                                        <div class="col-lg-4 sidebar-view">
                                            @include('dashboard.users.components.editpartials._sidebar')
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="leads">
                            @include('dashboard.users.components._leads')
                        </div>

                        <div class="tab-pane fade" id="tasks">
                            @include('dashboard.users.components._tasks')
                        </div>
                        <div class="tab-pane fade" id="projects">
                            @include('dashboard.users.components._projects')
                        </div>

                       
                    </div>
                </div>
            </div>

        </div>



    </div>
@endsection




@push('scripts')
    <script>
        $("#editToggle").click(function(e) {
            e.preventDefault();
            $("#viewDetails").toggle();
            $("#editDetails").toggle();
            $("#updateBtn").toggle();
        })
    </script>
@endpush
