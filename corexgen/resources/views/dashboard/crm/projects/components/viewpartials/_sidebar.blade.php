<h6 class="detail-label">Sidebar</h6>
<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Tasks</h6>
            <h3 class="stat-value">{{ $project?->getTotalTasksCount() ?? 0 }}</h3>
        </div>
    </div>
</div>
<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Timesheets</h6>
            <h3 class="stat-value">{{ $project?->getTimeSheet() ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-sticky-note"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Notes / Comments</h6>
            <h3 class="stat-value">{{ $project?->getTotalNotesCount() ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-paperclip"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Attachments</h6>
            <h3 class="stat-value">{{ $project?->getTotalAttachmentsCount() ?? 0 }}</h3>
        </div>
    </div>
</div>

<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <h6 class="stat-label">Total TimeSpend</h6>
            <h3 class="stat-value">{{ convertMinutesToHoursAndMinutes($project?->getTotalTimeSpentOnTasks()) ?? 0 }}</h3>
        </div>
    </div>
</div>