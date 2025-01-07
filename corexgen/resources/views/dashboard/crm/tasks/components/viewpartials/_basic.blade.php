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
        <label>Company Name</label>
        <p>{{ $lead->company_name }}</p>
    </div>
    <div class="detail-group">
        <label>First Name</label>
        <p>{{ $lead->first_name }}</p>
    </div>
    <div class="detail-group">
        <label>Last Name</label>
        <p>{{ $lead->last_name }}</p>
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