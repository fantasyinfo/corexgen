@push('style')
    <style>
        .profile-container {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .profile-header {
            display: flex;

            align-items: center;

            gap: 15px;

            background-color: var(--primary-color);
            color: var(--body-bg);

            padding: 15px;

            border-radius: 10px;

            max-width: 100%;

            overflow: hidden;

        }

        .profile-avatar {
            width: 60px;

            height: 60px;
            border-radius: 50%;

            object-fit: cover;

        }

        .address-section {
            padding: 20px;
            background-color: var(--light-color);
            border-bottom: 1px solid var(--border-color);
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .contact-icon {
            background-color: var(--primary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .contact-details {
            flex-grow: 1;
        }

        .contact-label {
            color: var(--neutral-gray);
            font-size: 0.75rem;
            margin-bottom: 2px;
        }

        .contact-value {
            color: var(--body-color);
            font-weight: 500;
            word-break: break-word;
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            color: var(--neutral-gray);
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }

        .tab-content {
            background-color: var(--card-bg);
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        /* Timeline Styling */
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 2px;
            height: 100%;
            background-color: var(--secondary-color);
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -37px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }

        .timeline-item:last-child {
            border-bottom: none;
        }

        .timeline-date {
            color: #6c757d;
            font-size: 0.8rem;
        }
    </style>
@endpush
@extends('layout.app')

@section('content')
    <div class="row profile-container">
        <!-- Profile Details Column -->
        <div class="col-md-4 p-0">
            <div class="profile-header">
                <img src="/api/placeholder/150/150" alt="User Avatar" class="profile-avatar">
                <h6 class="mb-1">{{ $user->name }}</h6>
      
            </div>

            <!-- Address Section -->
            <div class="address-section">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <div class="contact-label">Email</div>
                        <div class="contact-value">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <div class="contact-label">Phone</div>
                        <div class="contact-value">{{ $user->phone }}</div>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <div class="contact-label">Address</div>
                        <div class="contact-value">
                            {{ @$user->addresses->street_address }}<br>
                            {{ @$user->addresses->city->name }}, {{ @$user->addresses->country->code }}
                            {{ @$user->addresses->postal_code }}
                        </div>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="contact-details">
                        <div class="contact-label">Role</div>
                        <div class="contact-value">{{ $user->roles->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Column -->
        <div class="col-md-8 p-0">
            <div class="card border-0 rounded-0">
                <div class="card-header bg-transparent border-0 pt-4">
                    <ul class="nav nav-tabs" id="userActivityTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="leads-tab" data-bs-toggle="tab" data-bs-target="#leads"
                                type="button" role="tab">Leads</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects"
                                type="button" role="tab">Projects</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content p-4" id="userActivityContent">
                    <!-- Leads Tab -->
                    <div class="tab-pane fade show active" id="leads" role="tabpanel">
                        <div class="timeline">
                            <div class="timeline-item">
                                <strong>New Lead: Acme Corporation</strong>
                                <div class="text-muted small">2 hours ago</div>
                            </div>
                            <div class="timeline-item">
                                <strong>Lead Status Updated: TechStart Inc.</strong>
                                <div class="text-muted small">Yesterday</div>
                            </div>
                            <div class="timeline-item">
                                <strong>Converted Lead: Global Solutions</strong>
                                <div class="text-muted small">3 days ago</div>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Tab -->
                    <div class="tab-pane fade" id="projects" role="tabpanel">
                        <div class="timeline">
                            <div class="timeline-item">
                                <strong>Project Started: Website Redesign</strong>
                                <div class="text-muted small">1 week ago</div>
                            </div>
                            <div class="timeline-item">
                                <strong>Project Milestone: Mobile App Development</strong>
                                <div class="text-muted small">2 weeks ago</div>
                            </div>
                            <div class="timeline-item">
                                <strong>Project Completed: E-commerce Platform</strong>
                                <div class="text-muted small">1 month ago</div>
                            </div>
                        </div>
                    </div>

                  
                </div>
            </div>
        </div>
    </div>
@endsection
