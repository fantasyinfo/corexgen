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

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stat-card" style="border-bottom: 2px solid #4e73df;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Companies</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCompanies ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Revenue</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ getSettingValue('Currency Symbol') }}{{ number_format($monthlyRevenue ?? 0) }}</div>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Subscriptions
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeSubscriptions ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sync fa-2x text-gray-300"></i>
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
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? 0 }}</div>
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
            <!-- Revenue Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Distribution -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Plan Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="planDistribution"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       @include('dashboard.home.components.super._recentActivity')
    </div>
@endsection

@push('scripts')
    <!-- chart2js -->
    <script src="{{ asset('js/chart2/chart2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueData = @json($revenueData);
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueData.labels,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: revenueData.data,
                        fill: true,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        tension: 0.3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
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

            // Plan Distribution Chart
            const planCtx = document.getElementById('planDistribution').getContext('2d');
            const planData = @json($planData);

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


            const backgroundColors = generateColors(planData.labels.length);
            new Chart(planCtx, {
                type: 'doughnut',
                data: {
                    labels: planData.labels,
                    datasets: [{
                        data: planData.data,
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

           

            // Generate colors dynamically for the chart
          
        });
    </script>
@endpush
