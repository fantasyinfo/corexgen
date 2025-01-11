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
                <h1 class="h3 mb-2 text-gray-800">Dashboard</h1>
                <p class="text-muted">Welcome back, {{ Auth::user()->name }}</p>
            </div>
        </div>

        <!-- Primary Stats Cards Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stat-card" style="border-bottom: 2px solid #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Projects</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $activeProjects['current_month'] ?? 0 }}</div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-{{ $activeProjects['trend'] }}">
                                        {{ $activeProjects['trend'] === 'up' ? '↑' : '↓' }}
                                        {{ abs($activeProjects['percentage_change']) }}% vs last month
                                    </span>
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
                <div class="card border-left-success shadow h-100 py-2 stat-card" style="border-bottom: 2px solid #1cc88a;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $revenue['current_month'] ?? 0 }}
                                </div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-{{ $revenue['trend'] }}">
                                        {{ $revenue['trend'] === 'up' ? '↑' : '↓' }}
                                        {{ abs($revenue['percentage_change']) }}% vs last month
                                    </span>
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
                <div class="card border-left-info shadow h-100 py-2 stat-card" style="border-bottom: 2px solid #36b9cc;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Tasks</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $tasks['current_month'] ?? 0 }}</div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-{{ $tasks['trend'] }}">
                                        {{ $tasks['trend'] === 'up' ? '↑' : '↓' }}
                                        {{ abs($tasks['percentage_change']) }}% vs last month
                                    </span>
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
                <div class="card border-left-warning shadow h-100 py-2 stat-card" style="border-bottom: 2px solid #f6c23e;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Clients</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $clients['current_month'] ?? 0 }}
                                </div>
                                <div class="mini-stat">
                                    <span class="trend-indicator trend-{{ $clients['trend'] }}">
                                        {{ $clients['trend'] === 'up' ? '↑' : '↓' }}
                                        {{ abs($clients['percentage_change']) }}% vs last month
                                    </span>
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



        @include('dashboard.home.components.company._recentInvoices')
        @include('dashboard.home.components.company._recentActivity')



    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart2/chart2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Project Timeline Chart
            const timelineCtx = document.getElementById('projectTimeline').getContext('2d');
            const projectTimelines = @json($projectsTimelines);
            new Chart(timelineCtx, {
                type: 'bar',
                data: {
                    labels: projectTimelines.labels,
                    datasets: [{
                        label: 'Completed Projects',
                        data: projectTimelines.completedProjects,
                        backgroundColor: '#4e73df'
                    }, {
                        label: 'New Projects',
                        data: projectTimelines.newProjects,
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
            const tasksCounts = @json($tasksCounts);

            const generateColors = (count) => {
                const colors = [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#6f42c1', '#fd7e14', '#20c997', '#dc3545'
                ]; // Pool of colors
                const result = [];
                for (let i = 0; i < count; i++) {
                    result.push(colors[i % colors.length]); // Reuse colors if plans exceed the pool size
                }
                return result;
            };

            const backgroundColors = generateColors(tasksCounts.labels.length);


            new Chart(taskCtx, {
                type: 'doughnut',
                data: {
                    labels: tasksCounts.labels,
                    datasets: [{
                        data: tasksCounts.data,
                        backgroundColor: backgroundColors,
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


        });
    </script>
@endpush
