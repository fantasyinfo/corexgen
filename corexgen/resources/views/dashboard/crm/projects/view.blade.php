@extends('layout.app')
@push('style')
    <style>
        #projectView #editDetails,
        #updateBtn {
            display: none;
        }

        #projectView .divider {
            background-color: var(--body-bg);
        }

        #projectView .card {
            background-color: var(--card-bg);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.3s ease;
        }

        #projectView .card:hover {
            /* transform: translateY(-2px); */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Lead Header Styles */
        /* Lead Header Styles */
        #projectView .lead-header-card {
            background: var(--card-bg);
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        #projectView .lead-avatar {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 600;
        }

        #projectView .company-avatar {
            background: var(--primary-color);
            color: white;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }

        #projectView .individual-avatar {
            background: var(--secondary-color);
            color: white;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: inherit;
        }

        #projectView .lead-score {
            background: var(--light-color);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
        }

        /* Stat Cards */
        #projectView .stat-card {
            border-radius: 1rem;
            background: var(--card-bg);
        }

        #projectView .stat-icon {
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

        #projectView .stat-label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        #projectView .stat-value {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        /* Tabs Styling */
        #projectView .nav-tabs {
            border-bottom: none;
        }

        #projectView .nav-tabs .nav-link {
            border: none;
            color: var(--neutral-gray);
            padding: 1rem 1.5rem;
            font-weight: 500;
            position: relative;
        }

        #projectView .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background: transparent;
            border: none;
        }

        #projectView .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary-color);
        }

        /* Detail Sections */
        #projectView .detail-label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        #projectView .detail-group {
            margin-bottom: 1rem;
        }

        #projectView .detail-group label {
            color: var(--neutral-gray);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        #projectView .detail-group p {
            margin: 0;
            font-weight: 500;
        }

        #projectView .timeline-wrapper {
            position: relative;
            padding: 1rem 0;
        }

        #projectView .timeline {
            position: relative;
            padding-left: 3rem;
        }

        #projectView .timeline::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 0;
            height: 100%;
            width: 2px;
            background: var(--body-bg);
        }

        #projectView .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }

        #projectView .timeline-icon {
            position: absolute;
            left: -3rem;
            width: 30px;
            height: 30px;
        }

        #projectView .icon-wrapper {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }

        #projectView .timeline-content-wrapper {
            /* background: var(--body-bg); */
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }



        #projectView .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        #projectView .activity-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        #projectView .activity-title h6 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        #projectView .activity-time {
            font-size: 0.875rem;
            color: var(--neutral-gray);
        }

        #projectView .activity-changes {
            background: var(--input-bg);
            border-radius: 0.5rem;
            /* padding: 0.75rem; */
        }

        #projectView .change-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* padding: 0.5rem 0; */
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border-color);
        }

        #projectView .change-item:last-child {
            border-bottom: none;
        }

        #projectView .field-label {
            font-weight: 500;
            color: var(--body-color);
        }

        #projectView .change-values {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }

        #projectView .old-value {
            color: var(--danger-color);
            text-decoration: line-through;
        }

        #projectView .new-value {
            color: var(--success-color);
        }

        #projectView .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        #projectView .empty-state-icon {
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
        #projectView .task-list {
            padding: 1rem;
        }

        #projectView .task-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background: var(--input-bg);
            margin-bottom: 0.5rem;
        }

        /* Badge Styling */
        #projectView .badge {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
        }

        #projectView .bg-priority-high {
            background-color: var(--danger-color);
            color: white;
        }

        #projectView .bg-priority-medium {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        #projectView .bg-priority-low {
            background-color: var(--success-color);
            color: white;
        }

        /* Button Styling */
        #projectView .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.5rem;
            /* transition: all 0.3s ease; */
        }

        #projectView .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        #projectView .btn-primary:hover {
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
            #projectView .lead-header-card .d-flex {
                flex-direction: column;
            }

            #projectView .stat-card {
                margin-bottom: 1rem;
            }

            #projectView .nav-tabs .nav-link {
                padding: 0.75rem 1rem;
            }
        }

        /* Animation Effects */
        /* #projectView .card,
        #projectView .btn,
        #projectView .badge {
            transition: all 0.3s ease;
        } */

        #projectView .divider-container {
            display: flex;
            justify-content: center;
            align-items: stretch;
        }

        #projectView .divider {
            width: 1px;
            background-color: var(--body-bg);
            /* Adjust color as per your theme */
            height: 100%;
        }

        #projectView .main-content-view {
            padding-right: 1rem;
            /* Add space near the divider */
        }

        #projectView .sidebar-view {
            padding-left: 1rem;
            /* Add space near the divider */
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        let currentTheme = document.documentElement.getAttribute('data-bs-theme');
    </script>
@endpush
@section('content')
    @php
        //  prePrintR($project->attachments->toArray());
    @endphp
    <div class="container-fluid " id="projectView">
        <!-- Lead Header -->
        <div class="card mb-4 border-0 pb-0 lead-header-card">
            <div class="card-body">
                @include('dashboard.crm.projects.components._header')
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
                            <a class="nav-link" data-bs-toggle="tab" href="#tasks">
                                <i class="fas fa-tasks me-2"></i>Tasks
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#timesheets">
                                <i class="fas fa-clock me-2"></i>Timesheets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#invoicesTab">
                                <i class="fas fa-receipt me-2"></i>Invoices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#milestones">
                                <i class="fas fa-rocket me-2"></i>Milestones
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#proposals">
                                <i class="fas fa-flag me-2"></i>Proposals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#estimates">
                                <i class="fas fa-file-signature me-2"></i>Estimates
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#contracts">
                                <i class="fas fa-file-contract me-2"></i>Contracts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#activities">
                                <i class="fas fa-history me-2"></i>Activities
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#notesTab">
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
                <div class="card-body mt-0 pt-0">
                    <div class="tab-content">
                        <!-- Details View Tab -->
                        <div class="tab-pane fade show active" id="details">

                            <!-- View Details -->
                            <div class="row g-4" id="viewDetails">
                                <div class="col-lg-7 main-content-view">
                                    <div class="row">
                                        @include('dashboard.crm.projects.components.viewpartials._basic')
                                        @include('dashboard.crm.projects.components.viewpartials._additional')
                                    </div>
                                    @include('dashboard.crm.projects.components.viewpartials._details')

                                </div>
                                <div class="col-lg-1 divider-container">
                                    <div class="divider"></div>
                                </div>
                                <div class="col-lg-4 sidebar-view">
                                    @include('dashboard.crm.projects.components.viewpartials._sidebar')
                                </div>
                               
                            </div>


                        </div>

                        <div class="tab-pane fade" id="tasks">
                            @include('dashboard.crm.projects.components._tasks')
                        </div>
                        <div class="tab-pane fade" id="timesheets">
                            @include('dashboard.crm.projects.components._timesheets')
                        </div>
                        <div class="tab-pane fade" id="invoicesTab">
                            @include('dashboard.crm.projects.components._invoices')
                        </div>
                        <div class="tab-pane fade" id="milestones">
                            @include('dashboard.crm.projects.components._milestones')
                        </div>

                        <div class="tab-pane fade" id="proposals">
                            @include('dashboard.crm.projects.components._proposals')
                        </div>

                        <div class="tab-pane fade" id="estimates">
                            @include('dashboard.crm.projects.components._estimates')
                        </div>
                        <div class="tab-pane fade" id="contracts">
                            @include('dashboard.crm.projects.components._contracts')
                        </div>

                        <div class="tab-pane fade" id="activities">
                            @include('dashboard.crm.projects.components._activity')
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notesTab">
                            @include('dashboard.crm.projects.components._notes')
                        </div>

                        <!-- Files Tab -->
                        <div class="tab-pane fade" id="files">
                            @include('dashboard.crm.projects.components._attachments')
                        </div>
                    </div>
                </div>
            </div>

        </div>



    </div>
@endsection
