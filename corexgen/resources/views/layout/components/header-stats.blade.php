<style>
    .stats-card {
        background: var(--card-bg);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: transform 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
    }

    .stats-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-right: 1rem;
    }

    .total-users-icon {
        background: rgba(103, 61, 230, 0.1);
        color: var(--primary-color);
    }

    .active-users-icon {
        background: rgba(0, 176, 144, 0.1);
        color: var(--success-color);
    }

    .inactive-users-icon {
        background: rgba(255, 60, 92, 0.1);
        color: var(--danger-color);
    }

    .stats-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--body-color);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stats-label {
        color: var(--neutral-gray);
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-meta {
        font-size: 0.813rem;
        color: var(--neutral-gray);
    }

    .progress-bar-container {
        height: 3px;
        background: var(--border-color);
        border-radius: 1.5px;
        margin-top: 0.5rem;
    }

    .progress-bar {
        height: 100%;
        border-radius: 1.5px;
        background: var(--primary-color);
        transition: width 0.3s ease;
    }
</style>

<div class="row g-3 my-2">
    <!-- Total Users Card -->
    <div class="col-md-4">
        <div class="stats-card p-3">
            <div class="d-flex align-items-center">
                <div class="stats-icon total-users-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stats-value">{{ $total_used }}</div>
                    <div class="stats-label">Total {{$type}}</div>
                    <div class="stats-meta">
                        {{ $total_allow == '-1' ? '∞' : $total_allow }} Allowed
                        @if($total_allow != '-1')
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: {{ ($total_used / $total_allow) * 100 }}%"></div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Users Card -->
    <div class="col-md-4">
        <div class="stats-card p-3">
            <div class="d-flex align-items-center">
                <div class="stats-icon active-users-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <div class="stats-value">{{ $total_active }}</div>
                    <div class="stats-label">Active {{$type}}</div>
                    <div class="stats-meta">
                        {{ $total_ussers > 0 ? number_format(($total_active / $total_ussers) * 100, 1) : 0 }}% of total
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive Users Card -->
    <div class="col-md-4">
        <div class="stats-card p-3">
            <div class="d-flex align-items-center">
                <div class="stats-icon inactive-users-icon">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <div class="stats-value">{{ $total_inactive }}</div>
                    <div class="stats-label">Inactive {{$type}}</div>
                    <div class="stats-meta">
                        {{ $total_ussers > 0 ? number_format(($total_inactive / $total_ussers) * 100, 1) : 0 }}% of total
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>