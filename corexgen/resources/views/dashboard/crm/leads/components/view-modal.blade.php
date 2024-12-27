@push('style')
<style>
 

    #viewLeadModal  .modal-header {
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

    #viewLeadModal  .avatar-group {
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

    #viewLeadModal  .add-member:hover {
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

    #viewLeadModal  .status-badge:hover {
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

    #viewLeadModal  .tab-container {
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
  </style>
@endpush

<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-labelledby="viewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary me-2">8/12</span>
                    <h5 class="modal-title mb-0">Leads Title</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                
                    <button class="btn btn-link text-muted p-0" id="actionMenuBtn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="action-menu d-none">
                <a href="#" class="action-menu-item"><i class="fas fa-edit"></i> Edit</a>
                <a href="#" class="action-menu-item"><i class="fas fa-user"></i> Assign to</a>
                <a href="#" class="action-menu-item"><i class="fas fa-paperclip"></i> Attach files</a>
                <a href="#" class="action-menu-item"><i class="fas fa-tag"></i> Apply Labels</a>
                <a href="#" class="action-menu-item"><i class="fas fa-calendar"></i> Set Due Date</a>
                <a href="#" class="action-menu-item"><i class="fas fa-bookmark"></i> Follow Task</a>
                <a href="#" class="action-menu-item"><i class="fas fa-arrow-up"></i> Set as Top Priority</a>
                <a href="#" class="action-menu-item"><i class="fas fa-sync"></i> Change Status</a>
                <a href="#" class="action-menu-item"><i class="fas fa-copy"></i> Save as Template</a>
                <a href="#" class="action-menu-item"><i class="fas fa-archive"></i> Move to archive</a>
                <a href="#" class="action-menu-item text-danger"><i class="fas fa-trash"></i> Delete</a>
            </div>

            <div class="modal-body">
                <div class="private-badge">
                    <i class="fas fa-lock"></i>
                    This task is private for Development Team
                </div>

                <p class="text-muted mb-4">
                    Lead Title
                </p>

                <div class="avatar-group mb-4">
                    <div class="avatar">J</div>
                    <div class="avatar bg-success">M</div>
                    <div class="avatar bg-secondary">T</div>
                    <div class="avatar bg-primary">R</div>
                    <button class="add-member" title="Add team member">+</button>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-3">Assignee By</h6>
                        <div class="d-flex align-items-center">
                            <div class="avatar teal me-2">H</div>
                            <div>
                                <div class="fw-medium">Gaurav Sharma</div>
                                <small class="text-muted">4 July 2022, 8:30pm</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-3">Follow Up Date</h6>
                        <div class="date-badge">
                            <i class="far fa-calendar me-1"></i>
                            27 Dec 2024, 7:00 PM
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-3">Stage</h6>
                        <div class="badge bg-success">
                            <i class="fas fa-spinner me-1"></i>
                            Qualified
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-muted mb-3">Priority & Groups & Source</h6>
                    <span class="task-label bg-danger">High </span>
                    <span class="task-label bg-success">Hot </span>
                    <span class="task-label bg-info">Ads </span>
   
                </div>

                <div class="tab-container">
                    <button class="tab-button active" data-tab="checklist">
                        Checklist
                    </button>
                    <button class="tab-button" data-tab="comments">Comments</button>
                    <button class="tab-button" data-tab="files">Files</button>
                    <button class="tab-button" data-tab="activity">Activity</button>
                </div>

                <div id="checklist" class="tab-content active">
                    <div class="checklist-item">
                        <input type="checkbox" class="form-check-input" id="task1" />
                        <label class="form-check-label" for="task1">Setup development environment</label>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="form-check-input" id="task2" checked />
                        <label class="form-check-label text-decoration-line-through" for="task2">Initialize project
                            structure</label>
                    </div>
                    <div class="checklist-item">
                        <input type="checkbox" class="form-check-input" id="task3" />
                        <label class="form-check-label" for="task3">Configure build tools</label>
                    </div>
                    <button class="btn btn-link p-0 mt-3">+ Add Item</button>
                </div>

                <div id="comments" class="tab-content">
                    <div class="comment-box">
                        <textarea class="comment-input" placeholder="Write a comment..." rows="3"></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button class="btn btn-link p-0 me-3">
                                    <i class="far fa-smile"></i>
                                </button>
                                <button class="btn btn-link p-0 me-3">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button class="btn btn-link p-0">
                                    <i class="fas fa-at"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary btn-sm">Comment</button>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="avatar teal">H</div>
                        <div class="activity-content">
                            <div class="fw-medium">Hencework</div>
                            <p class="mb-1">
                                The documentation has been updated with the latest changes.
                            </p>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                </div>

                <div id="files" class="tab-content">
                    <div class="file-item">
                        <div class="file-icon">
                            <i class="far fa-file-pdf"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">Documentation.pdf</div>
                            <small class="text-muted">Added by Morgan • 2.4 MB</small>
                        </div>
                        <button class="btn btn-link">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="file-item">
                        <div class="file-icon">
                            <i class="far fa-file-code"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">framework.js</div>
                            <small class="text-muted">Added by Jimmy • 156 KB</small>
                        </div>
                        <button class="btn btn-link">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <button class="btn btn-outline-primary btn-sm mt-3">
                        <i class="fas fa-upload me-2"></i>Upload Files
                    </button>
                </div>

                <div id="activity" class="tab-content">
                    <div class="activity-item">
                        <div class="avatar teal">H</div>
                        <div class="activity-content">
                            <div class="fw-medium">Hencework</div>
                            <p class="mb-1">
                                Updated documentation link -
                                <a href="#">https://hencework.com/theme/jampa</a>
                            </p>
                            <small class="text-muted">Oct 15, 2021, 12:34 PM</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="avatar pink">M</div>
                        <div class="activity-content">
                            <div class="fw-medium">Morgan Fregman</div>
                            <p class="mb-1">Completed react conversion of components</p>
                            <small class="text-muted">Sep 16, 2021, 4:54 PM</small>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="avatar">J</div>
                        <div class="activity-content">
                            <div class="fw-medium">Jimmy Carry</div>
                            <p class="mb-1">Completed side bar menu on elements</p>
                            <small class="text-muted">Sep 15, 2021, 2:30 PM</small>
                        </div>
                    </div>
                </div>
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
