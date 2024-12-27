<div class="modal fade" id="viewLeadModal" tabindex="-1" aria-labelledby="viewLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header  border-bottom">
                <h5 class="modal-title" id="viewLeadModalLabel">
                    <i class="fas fa-eye me-2"></i>View Lead Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Basic Info Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i>Basic Information
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    <strong>Company:</strong>
                                    <span class="ms-2" id="companyName"></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <strong>Contact:</strong>
                                    <span class="ms-2" id="contactName"></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-briefcase text-primary me-2"></i>
                                    <strong>Title:</strong>
                                    <span class="ms-2" id="title"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <strong>Email:</strong>
                                    <span class="ms-2" id="email"></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Phone:</strong>
                                    <span class="ms-2" id="phone"></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-dollar-sign text-primary me-2"></i>
                                    <strong>Value:</strong>
                                    <span class="ms-2" id="value"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Priority Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-2"></i>Status & Priority
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-tasks text-info me-2"></i>
                                    <strong>Status:</strong>
                                    <span class="badge bg-success ms-2" id="status"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-flag text-info me-2"></i>
                                    <strong>Priority:</strong>
                                    <span class="badge bg-warning ms-2" id="priority"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-star text-info me-2"></i>
                                    <strong>Score:</strong>
                                    <span class="ms-2" id="score"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dates Section -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-calendar me-2"></i>Important Dates
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone-square text-secondary me-2"></i>
                                    <strong>Last Contacted:</strong>
                                    <span class="ms-2" id="lastContactedDate"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-history text-secondary me-2"></i>
                                    <strong>Last Activity:</strong>
                                    <span class="ms-2" id="lastActivityDate"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-secondary me-2"></i>
                                    <strong>Follow Up:</strong>
                                    <span class="ms-2" id="followUpDate"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignees Section -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i>Assigned Team Members
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="assigneesList">
                            <!-- Assignees will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>