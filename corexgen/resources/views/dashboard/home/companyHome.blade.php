@extends('layout.app')

@push('style')
    <style>
        .stat-card {
            transition: all 0.3s;
            border-radius: 15px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
        }

        .project-progress {
            height: 8px;
            border-radius: 4px;
        }

        .mini-stat {
            font-size: 0.875rem;
            color: #666;
        }

        .trend-indicator {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .trend-up {
            background-color: rgba(28, 200, 138, 0.1);
            color: #1cc88a;
        }

        .trend-down {
            background-color: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2 text-gray-800">Company Dashboard</h1>
                <p class="text-muted">Welcome back, {{ Auth::user()->name }}</p>
            </div>
        </div>

        <!-- Primary Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stat-card" style="border-left: 4px solid #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Projects</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeProjects ?? 0 }}</div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-up">↑ 12% vs last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 stat-card" style="border-left: 4px solid #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($totalRevenue ?? 0) }}
                                </div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-up">↑ 8.5% vs last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 stat-card" style="border-left: 4px solid #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Tasks</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeTasks ?? 0 }}</div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-down">↓ 3% vs last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2 stat-card" style="border-left: 4px solid #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Clients</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClients ?? 0 }}</div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-up">↑ 5% vs last month</span>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stat-card" style="border-left: 4px solid #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Proposals
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingProposals ?? 0 }}</div>
                                <div class="mini-stat">Value: ${{ number_format($proposalValue ?? 0) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-signature fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 stat-card" style="border-left: 4px solid #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Contracts
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeContracts ?? 0 }}</div>
                                <div class="mini-stat">Value: ${{ number_format($contractValue ?? 0) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 stat-card" style="border-left: 4px solid #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Hours Logged</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $hoursLogged ?? 0 }}h</div>
                                <div class="mini-stat">This Month</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2 stat-card" style="border-left: 4px solid #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open Leads</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $openLeads ?? 0 }}</div>
                                <div class="mini-stat">Potential: ${{ number_format($leadValue ?? 0) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-funnel-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- Project Timeline -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Project Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="projectTimeline"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Distribution -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Task Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="taskDistribution"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue and Time Tracking -->
        <div class="row mb-4">
            <div class="col-xl-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Revenue & Expenses</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueExpenses"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Time Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="timeDistribution"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Progress and Recent Invoices -->
        <div class="row mb-4">
            <!-- Project Progress -->
            <div class="col-xl-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Active Projects Progress</h6>
                    </div>
                    <div class="card-body">
                        @foreach ($activeProjectsList ?? [] as $project)
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>{{ $project->name }}</span>
                                    <span class="text-muted">{{ $project->progress }}%</span>
                                </div>
                                <div class="progress project-progress">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $project->progress }}%"
                                        aria-valuenow="{{ $project->progress }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="col-xl-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Invoices</h6>
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentInvoices ?? [] as $invoice)
                                        <tr>
                                            <td>{{ $invoice->number }}</td>
                                            <td>{{ $invoice->client_name }}</td>
                                            <td>${{ number_format($invoice->amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $invoice->status_color }}">
                                                    {{ $invoice->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Tables Row -->
        <div class="row">
            <!-- Upcoming Deadlines -->
            <!-- Upcoming Deadlines -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Upcoming Deadlines</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Task</th>
                                        <th>Assigned To</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($upcomingDeadlines ?? [] as $deadline)
                                        <tr>
                                            <td>{{ $deadline->project_name }}</td>
                                            <td>{{ $deadline->task_name }}</td>
                                            <td>{{ $deadline->assigned_to }}</td>
                                            <td>{{ $deadline->due_date->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline-activity">
                            @foreach ($recentActivities ?? [] as $activity)
                                <div class="activity-item d-flex mb-3">
                                    <div class="activity-content flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $activity->user_name }}</strong>
                                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-0">{{ $activity->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart2/chart2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Project Timeline Chart
            const timelineCtx = document.getElementById('projectTimeline').getContext('2d');
            new Chart(timelineCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Completed Projects',
                        data: [4, 6, 5, 8, 7, 9],
                        backgroundColor: '#4e73df'
                    }, {
                        label: 'New Projects',
                        data: [6, 4, 7, 5, 8, 6],
                        backgroundColor: '#1cc88a'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Task Distribution Chart
            const taskCtx = document.getElementById('taskDistribution').getContext('2d');
            new Chart(taskCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'In Progress', 'Pending'],
                    datasets: [{
                        data: [45, 35, 20],
                        backgroundColor: ['#1cc88a', '#4e73df', '#f6c23e'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Revenue & Expenses Chart
            const revenueCtx = document.getElementById('revenueExpenses').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [15000, 18000, 17000, 21000, 24000, 28000],
                        borderColor: '#1cc88a',
                        tension: 0.3,
                        fill: false
                    }, {
                        label: 'Expenses',
                        data: [12000, 13000, 14000, 15000, 16000, 17000],
                        borderColor: '#e74a3b',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Time Distribution Chart
            const timeCtx = document.getElementById('timeDistribution').getContext('2d');
            new Chart(timeCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Development', 'Meetings', 'Planning', 'Research', 'Testing'],
                    datasets: [{
                        data: [30, 20, 15, 18, 17],
                        backgroundColor: [
                            '#4e73df',
                            '#1cc88a',
                            '#36b9cc',
                            '#f6c23e',
                            '#e74a3b'
                        ]
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@endpush
