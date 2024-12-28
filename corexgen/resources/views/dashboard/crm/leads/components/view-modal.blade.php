@push('style')
    <style>
        #viewLeadModal .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }

        #viewLeadModal .action-menu {
            position: absolute;
            right: 15px;
            top: 70px;
            width: 200px;
            background: var(--body-bg);
            border: 1px solid var(--border-bg);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        #viewLeadModal .action-menu-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--body-color);
            text-decoration: none;
            transition: background-color 0.2s;
        }

        #viewLeadModal .action-menu-item:hover {
            background-color: #f7fafc;
            color: var(--primary-color);
        }

        #viewLeadModal .avatar-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #viewLeadModal .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid white;
            margin-left: -8px;
        }

        #viewLeadModal .avatar:first-child {
            margin-left: 0;
        }



        #viewLeadModal .add-member {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #viewLeadModal .add-member:hover {
            background-color: #cbd5e0;
        }

        #viewLeadModal .status-badge {
            background-color: #fbd38d;
            color: #744210;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #viewLeadModal .status-badge:hover {
            background-color: #f6ad55;
        }

        #viewLeadModal .task-label {
            background-color: #e2e8f0;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            margin-right: 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        #viewLeadModal .task-label i {
            cursor: pointer;
            opacity: 0.6;
        }

        #viewLeadModal .task-label i:hover {
            opacity: 1;
        }

        #viewLeadModal .tab-container {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        #viewLeadModal .tab-button {
            border: none;
            background: none;
            padding: 12px 24px;
            font-size: 14px;
            color: #4a5568;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        #viewLeadModal .tab-button:hover {
            color: #2c5282;
        }

        #viewLeadModal .tab-button.active {
            color: #2c5282;
            border-bottom: 2px solid #2c5282;
            font-weight: 600;
        }

        #viewLeadModal .tab-content {
            display: none;
            padding: 20px 0;
        }

        #viewLeadModal .tab-content.active {
            display: block;
        }

        #viewLeadModal .checklist-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        #viewLeadModal .comment-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        #viewLeadModal .comment-input {
            border: none;
            width: 100%;
            resize: none;
            margin-bottom: 10px;
        }

        #viewLeadModal .comment-input:focus {
            outline: none;
        }

        #viewLeadModal .file-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        #viewLeadModal .file-icon {
            width: 40px;
            height: 40px;
            background-color: #ebf8ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c5282;
        }

        #viewLeadModal .activity-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        #viewLeadModal .activity-content {
            flex: 1;
        }

        #viewLeadModal .private-badge {
            background-color: #e6fffa;
            color: #234e52;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #viewLeadModal .date-badge {
            background-color: #f7fafc;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #4a5568;
        }
        #viewLeadModal .body-bg {
            background-color: var(--body-bg);
        }
    </style>
@endpush

<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-labelledby="viewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary me-2">8/12</span>
                    <h5 class="modal-title mb-0" id="title">Lead Title</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body body-bg">
                <!-- Lead Basic Details -->
                <div class="row g-4">
                    <!-- Company & Contact Information -->
                    <div class="col-md-6 ">
                        <h6 class="text-muted">Lead Details</h6>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th scope="row"><i class="fas fa-building"></i> Company</th>
                                    <td id="companyName">Company Name</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-user"></i> Contact</th>
                                    <td id="contactName">Contact Person Name</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-envelope"></i> Email</th>
                                    <td id="email">Contact Email</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-phone"></i> Phone</th>
                                    <td id="phone">Contact Phone</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-dollar-sign"></i> Value</th>
                                    <td id="value">Contact Value</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-phone"></i> Contact Method</th>
                                    <td id="pcm">Contact Method</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Status, Priority, and Stage -->
                    <div class="col-md-6">
                        <h6 class="text-muted">Stage, Priority, Source & Group</h6>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div id="stage">
                               
                            </div>
                            <div id="priority">
                               
                            </div>
                            <div  id="source">
                               
                            </div>
                            <div id="group">
                               
                            </div>
                            <div id="score">
                               
                            </div>
                        </div>
                        <h6 class="text-muted">Dates</h6>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th scope="row"><i class="fas fa-calendar"></i> Last Contacted</th>
                                    <td id="lastContactedDate">N/A</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-calendar"></i> Last Activity</th>
                                    <td id="lastActivityDate">N/A</td>
                                </tr>
                                <tr>
                                    <th scope="row"><i class="fas fa-calendar"></i> Follow Up</th>
                                    <td id="followUpDate">N/A</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assignees Section -->
                <div class="mt-4">
                    <h6 class="text-muted">Assignees</h6>
                    <div id="assigneesList" class="d-flex gap-3 flex-wrap">
                        <div class="card border-0 shadow-sm text-center" style="width: 100px;">
                            <img src="profile.jpg" alt="Assignee" class="card-img-top rounded-circle mt-3 mx-auto"
                                style="width: 64px; height: 64px;">
                            <div class="card-body">
                                <h6 class="card-title mb-0">John Doe</h6>
                            </div>
                        </div>
                        <!-- Additional assignees will be appended dynamically -->
                    </div>
                </div>

                <!-- Tabs for Additional Details -->
                {{-- <div class="mt-4">
                    <ul class="nav nav-tabs" id="leadDetailsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="comments-tab" data-bs-toggle="tab"
                                data-bs-target="#comments" type="button" role="tab">Comments</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files"
                                type="button" role="tab">Files</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity"
                                type="button" role="tab">Activity</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <div class="tab-pane fade show active" id="comments" role="tabpanel">
                            <p>No comments available.</p>
                        </div>
                        <div class="tab-pane fade" id="files" role="tabpanel">
                            <p>No files uploaded.</p>
                        </div>
                        <div class="tab-pane fade" id="activity" role="tabpanel">
                            <p>No recent activity.</p>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>











@push('scripts')
    <script>
        $(document).ready(function() {
            // Tab switching
            $(".tab-button").click(function() {
                $(".tab-button").removeClass("active");
                $(".tab-content").removeClass("active");
                $(this).addClass("active");
                $("#" + $(this).data("tab")).addClass("active");
            });

            // Action menu toggle
            $("#actionMenuBtn").click(function(e) {
                e.stopPropagation();
                $(".action-menu").toggleClass("d-none");
            });

            // Close action menu when clicking outside
            $(document).click(function() {
                $(".action-menu").addClass("d-none");
            });

            // Prevent modal from closing when clicking inside
            $(".action-menu").click(function(e) {
                e.stopPropagation();
            });

            // Status badge click handler
            $(".status-badge").click(function() {
                // Add status change functionality here
                console.log("Status clicked");
            });


            // Checkbox completion effect
            $(".form-check-input").change(function() {
                if (this.checked) {
                    $(this).next("label").addClass("text-decoration-line-through");
                } else {
                    $(this).next("label").removeClass("text-decoration-line-through");
                }
            });
        });
    </script>
@endpush
