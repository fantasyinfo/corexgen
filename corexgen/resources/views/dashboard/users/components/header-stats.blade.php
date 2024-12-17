<style>
    .card-stat-icon {
        background-color: rgba(103, 61, 230, 0.1);
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
    }

    .card-stat-icon i {
        color: var(--primary-color);
        font-size: 2.5rem;
    }

    .stat-value {
        color: var(--primary-color);
        font-weight: 700;
    }

    .stat-label {
        color: var(--neutral-gray);
        letter-spacing: 0.5px;
    }
</style>



<div class="row my-2">
    <!-- Total Users Card -->
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body d-flex align-items-center">
                <div>
                    <h6 class="stat-label text-uppercase mb-2">Total Users</h6>
                    <div class="d-flex align-items-baseline">
                        <h3 class="stat-value mb-0 me-2">5,000</h3>
                        <small class="text-muted">/ 6,000 Allowed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Users Card -->
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body d-flex align-items-center">

                <div>
                    <h6 class="stat-label text-uppercase mb-2">Active Users</h6>
                    <div class="d-flex align-items-baseline">
                        <h3 class="stat-value mb-0 me-2">3,200</h3>
                        <small class="text-muted">(64%)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive Users Card -->
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body d-flex align-items-center">

                <div>
                    <h6 class="stat-label text-uppercase mb-2">Inactive Users</h6>
                    <div class="d-flex align-items-baseline">
                        <h3 class="stat-value mb-0 me-2">1,800</h3>
                        <small class="text-muted">(36%)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
