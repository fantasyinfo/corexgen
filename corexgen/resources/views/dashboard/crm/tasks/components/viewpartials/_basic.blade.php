<div class="col-md-6">
    <h6 class="detail-label">Basic Information</h6>
    <div class="detail-group">
        <label>Title</label>
        <p>{{ $task->title }}</p>
    </div>
    <div class="detail-group">
        <label>Hourly Rate</label>
        <p>{{ number_format($task->hourly_rate,2) }}</p>
    </div>
    <div class="detail-group">
        <label>Start Date</label>
        <p>{{ formatDateTime($task->start_date) }}</p>
    </div>
    <div class="detail-group">
        <label>Due Date</label>
        <p>{{ formatDateTime($task->due_date) }}</p>
    </div>
    <div class="detail-group">
        <label>Related to</label>
        <p>{{ ucwords($task->related_to) }}</p>
    </div>
    <div class="detail-group">
        <label>Project</label>
        <p><a href="{{ route(getPanelRoutes('projects.view'),['id' => $task?->project?->id]) }}">{{ $task?->project?->title }}</a></p>
    </div>
</div>