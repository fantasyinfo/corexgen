<div class="col-md-6">
    <h6 class="detail-label">Basic Information</h6>
    <div class="detail-group">
        <label>Role</label>
        <p>{{ $user?->role?->role_name }}</p>
    </div>
    <div class="detail-group">
        <label>Name</label>
        <p>{{ $user?->name }}</p>
    </div>
    <div class="detail-group">
        <label>Company Name</label>
        <p>{{ $user?->company?->name }}</p>
    </div>
    <div class="detail-group">
        <label>Email</label>
        <p><a href="mailto:{{ $user?->email }}">{{ $user?->email }}</a></p>
    </div>
</div>