<h6 class="detail-label">Sidebar</h6>

<div class="card  border-0  mb-4">
    <div class="card-body d-flex gap-2">
        <div>
            @if (hasPermission(strtoupper($module) . '.' . $permissions['LOGIN_AS']['KEY']))
                <a class="btn btn-success" href="{{ route(getPanelRoutes($module . '.loginas'), $company->id) }}"
                    data-toggle="tooltip" title="Login to view">
                    <i class="fas fa-sign-in-alt me-2"></i>  Login to company
                </a>
            @endif
        </div>
    </div>
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-files"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Projects</h6>
            <h3 class="stat-value">{{ $company?->totalProjects() ?? 0 }}</h3>
        </div>
    </div>
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Clients</h6>
            <h3 class="stat-value">{{ $company?->totalClients() ?? 0 }}</h3>
        </div>
    </div>
    <div class="card-body d-flex gap-2">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h6 class="stat-label">Total Users</h6>
            <h3 class="stat-value">{{ $company?->totalUsers() ?? 0 }}</h3>
        </div>
    </div>
</div>
