<style>
    .stats-card {
        background: var(--card-bg, #ffffff);
        border-radius: 12px;
        border: 1px solid var(--border-color, #e5e7eb);
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 1rem;
    }

    .icon-primary {
        background: rgba(103, 61, 230, 0.1);
        color: #673de6;
    }

    .icon-success {
        background: rgba(0, 176, 144, 0.1);
        color: #00b090;
    }

    .icon-danger {
        background: rgba(255, 60, 92, 0.1);
        color: #ff3c5c;
    }

    .icon-warning {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }

    .stats-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--body-color, #1a1f36);
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .stats-label {
        color: var(--neutral-gray, #64748b);
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-meta {
        font-size: 0.875rem;
        color: var(--neutral-gray, #64748b);
        margin-top: 0.5rem;
    }

    .progress-bar-container {
        height: 4px;
        background: var(--border-color, #e5e7eb);
        border-radius: 2px;
        margin-top: 0.75rem;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    .progress-primary {
        background: #673de6;
    }

    .progress-success {
        background: #00b090;
    }

    .progress-danger {
        background: #ff3c5c;
    }

    .icon-open {
        background: rgba(103, 61, 230, 0.1);
        color: #673de6;
    }

    .icon-accepted {
        background: rgba(0, 176, 144, 0.1);
        color: #00b090;
    }

    .icon-declined {
        background: rgba(255, 60, 92, 0.1);
        color: #ff3c5c;
    }

    .icon-revise {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }

    .icon-sent {
        background: rgba(79, 129, 189, 0.1);
        color: #4f81bd;
    }

    /* User status colors */
    .icon-active {
        background: rgba(0, 176, 144, 0.1);
        color: #00b090;
    }

    .icon-inactive {
        background: rgba(156, 163, 175, 0.1);
        color: #9ca3af;
    }

    .icon-banned {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .icon-active,
    .icon-success {
        background: rgba(0, 176, 144, 0.1);
        color: #00b090;
    }

    /* Pending/In Progress States */
    .icon-pending,
    .icon-in-progress,
    .icon-testing {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }

    /* Inactive/Declined States */
    .icon-inactive,
    .icon-declined,
    .icon-disqualified {
        background: rgba(156, 163, 175, 0.1);
        color: #9ca3af;
    }

    /* Warning/Review States */
    .icon-revise,
    .icon-issue {
        background: rgba(255, 91, 0, 0.1);
        color: #ff5b00;
    }

    /* New/Open States */
    .icon-new,
    .icon-open {
        background: rgba(103, 61, 230, 0.1);
        color: #673de6;
    }

    /* Complete/Finished States */
    .icon-completed,
    .icon-converted {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    /* Communication States */
    .icon-sent,
    .icon-contacted,
    .icon-proposal-sent {
        background: rgba(79, 129, 189, 0.1);
        color: #4f81bd;
    }

    /* Qualified/Verified States */
    .icon-qualified {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    /* Awaiting States */
    .icon-awaiting-feedback {
        background: rgba(192, 132, 252, 0.1);
        color: #c084fc;
    }

    /* Banned/Critical States */
    .icon-banned {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }
</style>


<div class="row g-3 my-2">
    <!-- Total Stats Card -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="stats-card p-3">
            <div class="d-flex align-items-center">
                <div class="stats-icon icon-primary">
                    @switch(strtolower($type))
                        @case('users')
                            <i class="fas fa-users fa-lg"></i>
                        @break

                        @case('companies')
                            <i class="fas fa-building fa-lg"></i>
                        @break

                        @case('estimates')
                            <i class="fas fa-file-invoice fa-lg"></i>
                        @break

                        @case('contracts')
                            <i class="fas fa-file-contract fa-lg"></i>
                        @break

                        @default
                            <i class="fas fa-chart-line fa-lg"></i>
                    @endswitch
                </div>
                <div>
                    <div class="stats-value">{{ number_format($headerStatus['currentUsage']) }}</div>
                    <div class="stats-label">Total {{ $type }}</div>
                    <div class="stats-meta">
                        {{ $headerStatus['totalAllow'] == '-1' ? '∞' : number_format($headerStatus['totalAllow']) }}
                        Allowed
                        @if ($headerStatus['totalAllow'] != '-1')
                            <div class="progress-bar-container">
                                <div class="progress-bar progress-primary"
                                    style="width: {{ min(($headerStatus['currentUsage'] / $headerStatus['totalAllow']) * 100, 100) }}%">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Groups Cards -->
    @foreach ($headerStatus['groupData'] as $item)
        <div class="col-12 col-md-6 col-lg-3">
            <div class="stats-card p-3">
                <div class="d-flex align-items-center">

                    <div
                        class="stats-icon {{ match (strtolower($item['status'])) {
                            'active', 'success' => 'icon-active',
                            'inactive' => 'icon-inactive',
                            'banned' => 'icon-banned',
                            'open', 'new' => 'icon-open',
                            'accepted' => 'icon-success',
                            'declined', 'disqualified' => 'icon-declined',
                            'revise', 'issue' => 'icon-revise',
                            'sent', 'contacted', 'proposal sent' => 'icon-sent',
                            'pending', 'in progress', 'testing' => 'icon-pending',
                            'completed', 'converted' => 'icon-completed',
                            'awaiting feedback' => 'icon-awaiting-feedback',
                            'qualified' => 'icon-qualified',
                            default => 'icon-primary',
                        } }}">
                        @switch(strtolower($item['status']))
                            @case('active')
                                <i class="fas fa-user-check fa-lg" title="Active"></i>
                            @break

                            @case('inactive')
                                <i class="fas fa-user-clock fa-lg" title="Inactive"></i>
                            @break

                            @case('banned')
                                <i class="fas fa-user-slash fa-lg" title="Banned"></i>
                            @break

                            @case('open')
                                <i class="fas fa-folder-open fa-lg" title="Open"></i>
                            @break

                            @case('accepted')
                                <i class="fas fa-check-circle fa-lg" title="Accepted"></i>
                            @break

                            @case('declined')
                                <i class="fas fa-times-circle fa-lg" title="Declined"></i>
                            @break

                            @case('revise')
                                <i class="fas fa-edit fa-lg" title="Needs Revision"></i>
                            @break

                            @case('sent')
                                <i class="fas fa-paper-plane fa-lg" title="Sent"></i>
                            @break

                            @case('success')
                                <i class="fas fa-check-double fa-lg" title="Success"></i>
                            @break

                            @case('pending')
                                <i class="fas fa-clock fa-lg" title="Pending"></i>
                            @break

                            @case('new')
                                <i class="fas fa-star fa-lg" title="New"></i>
                            @break

                            @case('awaiting feedback')
                                <i class="fas fa-comment-dots fa-lg" title="Awaiting Feedback"></i>
                            @break

                            @case('completed')
                                <i class="fas fa-flag-checkered fa-lg" title="Completed"></i>
                            @break

                            @case('in progress')
                                <i class="fas fa-spinner fa-lg" title="In Progress"></i>
                            @break

                            @case('issue')
                                <i class="fas fa-exclamation-triangle fa-lg" title="Issue"></i>
                            @break

                            @case('testing')
                                <i class="fas fa-vial fa-lg" title="Testing"></i>
                            @break

                            @case('contacted')
                                <i class="fas fa-phone-alt fa-lg" title="Contacted"></i>
                            @break

                            @case('converted')
                                <i class="fas fa-exchange-alt fa-lg" title="Converted"></i>
                            @break

                            @case('disqualified')
                                <i class="fas fa-user-times fa-lg" title="Disqualified"></i>
                            @break

                            @case('proposal sent')
                                <i class="fas fa-file-export fa-lg" title="Proposal Sent"></i>
                            @break

                            @case('qualified')
                                <i class="fas fa-user-shield fa-lg" title="Qualified"></i>
                            @break

                            @default
                                <i class="fas fa-circle fa-lg"></i>
                        @endswitch
                    </div>
                    <div>
                        <div class="stats-value">{{ number_format($item['count']) }}</div>
                        <div class="stats-label">{{ $item['status'] }} {{ $type }}</div>
                        <div class="stats-meta">
                            {{ $headerStatus['currentUsage'] > 0 ? number_format(($item['count'] / $headerStatus['currentUsage']) * 100, 1) : 0 }}%
                            of total
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
