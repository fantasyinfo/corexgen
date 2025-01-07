<div class="col-md-6">
    <h6 class="detail-label">Basic Information</h6>

    <div class="detail-group">
        <label>Title</label>
        <p>{{ $project->title }}</p>
    </div>
    <div class="detail-group">
        <label>Client Type</label>
        <p>{{ $project->client?->type }}</p>
    </div>
    <div class="detail-group">
        <label>Company Name (if type company)</label>
        <p>{{ $project->client?->company_name }}</p>
    </div>
    <div class="detail-group">
        <label>Client Name</label>
        <p>
            <a href="{{ route(getPanelRoutes('clients.view'), ['id' => $project->client?->id]) }}">{{ $project->client?->first_name }}
                {{ $project->client?->last_name }}</a>
        </p>
    </div>

    <div class="detail-group">
        <label>Email</label>
        <p><a href="mailto:{{ $project->client?->primary_email }}">{{ $project->client?->primary_email }}</a></p>
    </div>
    <div class="detail-group">
        <label>Phone</label>
        <p><a href="tel:{{ $project->client?->primary_phone }}">{{ $project->client?->primary_phone }}</a></p>
    </div>
</div>
