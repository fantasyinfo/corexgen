<div class="col-md-6">
    <h6 class="detail-label">Basic Information</h6>
    <div class="detail-group">
        <label>Admin</label>
        <p>{{ $company?->users[0]->name }}</p>
    </div>
    <div class="detail-group">
        <label>Plan</label>
        <p>{{ $company?->plans?->name }}</p>
    </div>
    <div class="detail-group">
        <label>Renew At</label>
        <p>{{ formatDateTime($company?->latestSubscription?->next_billing_date) }}</p>
    </div>
    <div class="detail-group">
        <label>Company Name</label>
        <p>{{ $company?->name }}</p>
    </div>
    <div class="detail-group">
        <label>Email</label>
        <p><a href="mailto:{{ $company?->email }}">{{ $company?->email }}</a></p>
    </div>
</div>
