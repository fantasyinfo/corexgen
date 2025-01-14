@extends('layout.app')

@push('style')
    <style>
        .search-table {
            width: 100%;
            border-collapse: collapse;
        }

        .search-table th,
        .search-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .search-table th {
            font-weight: bold;
        }

        .search-card {
            margin-bottom: 20px;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #888;
        }

        .search-table {
            width: 100%;
            border-collapse: collapse;
        }

        .search-table th,
        .search-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .search-table th {
            /* background-color: #f8f9fa; */
            font-weight: bold;
        }

        .search-card {
            margin-bottom: 20px;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            font-size: 18px;
            color: #888;
        }
    </style>
@endpush

@section('content')
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Search Results</h1>
            <div class="text-muted">
                Showing results for: <strong>"{{ request('q') ?? request('mobile_q') }}"</strong>
            </div>
        </div>


        @if (!empty($data))
            @foreach ($data as $key => $group)
                @if ($group['data']->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">{{ $group['title'] }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered ">
                                    <thead>
                                        <tr>
                                            @switch($key)
                                                @case('clients')
                                                    <th>Company/Name</th>
                                                    <th>Contact Info</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('leads')
                                                    <th>Lead Name</th>
                                                    <th>Company</th>
                                                    <th>Contact Info</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('proposals')
                                                    <th>Id</th>
                                                    <th>Title</th>
                                                    <th>Client</th>
                                                    <th>Creating Date</th>
                                                    <th>Valid Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('estimates')
                                                    <th>Id</th>
                                                    <th>Title</th>
                                                    <th>Client</th>
                                                    <th>Creating Date</th>
                                                    <th>Valid Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('contracts')
                                                    <th>Id</th>
                                                    <th>Title</th>
                                                    <th>Client</th>
                                                    <th>Creating Date</th>
                                                    <th>Valid Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('products_services')
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Price</th>
                                                    <th>Unit</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('invoices')
                                                    <th>Invoice #</th>
                                                    <th>Client</th>
                                                    <th>Amount</th>
                                                    <th>Issue Date</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('projects')
                                                    <th>Project Name</th>
                                                    <th>Client</th>
                                                    <th>Billing Type</th>
                                                    <th>Start Date</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('tasks')
                                                    <th>Task</th>
                                                    <th>Project</th>
                                                    <th>Hourly Rate</th>
                                                    <th>Due Date</th>
                                                    <th>Priority</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('customfields')
                                                    <th>Field Label</th>
                                                    <th>Type</th>
                                                    <th>Relation</th>
                                                @break

                                                @case('calender')
                                                    <th>Event</th>
                                                    <th>Type</th>
                                                    <th>Meeting</th>
                                                    <th>Date/Time</th>
                                                    <th>Priority</th>
                                                @break

                                                @case('users')
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('role')
                                                    <th>Role Name</th>
                                                    <th>Actions</th>
                                                @break

                                                @case('category_tags')
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Parent</th>
                                                @break

                                                @default
                                                    @foreach (array_keys((array) $group['data']->first()) as $field)
                                                        <th>{{ ucfirst(str_replace('_', ' ', $field)) }}</th>
                                                    @endforeach
                                            @endswitch
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group['data'] as $item)
                                            <tr>
                                                @switch($key)
                                                    @case('clients')
                                                        @include('dashboard.home.components.company.search._clients')
                                                    @break

                                                    @case('leads')
                                                        @include('dashboard.home.components.company.search._leads')
                                                    @break

                                                    @case('customfields')
                                                        @include('dashboard.home.components.company.search._customfields')
                                                    @break

                                                    @case('role')
                                                        @include('dashboard.home.components.company.search._role')
                                                    @break

                                                    @case('category_tags')
                                                        @include('dashboard.home.components.company.search._cat')
                                                    @break

                                                    @case('proposals')
                                                        @include('dashboard.home.components.company.search._proposals')
                                                    @break

                                                    @case('estimates')
                                                        @include('dashboard.home.components.company.search._estimates')
                                                    @break

                                                    @case('contracts')
                                                        @include('dashboard.home.components.company.search._contracts')
                                                    @break

                                                    @case('invoices')
                                                        @include('dashboard.home.components.company.search._invoices')
                                                    @break

                                                    @case('products_services')
                                                        @include('dashboard.home.components.company.search._products')
                                                    @break

                                                    @case('projects')
                                                        @include('dashboard.home.components.company.search._projects')
                                                    @break

                                                    @case('tasks')
                                                        @include('dashboard.home.components.company.search._tasks')
                                                    @break

                                                    @case('users')
                                                        @include('dashboard.home.components.company.search._users')
                                                    @break

                                                    @case('calender')
                                                        @include('dashboard.home.components.company.search._calender')
                                                    @break

                                                    @default
                                                        <td colspan="5">No specific handler for this module.</td>
                                                @endswitch
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                <div class="no-results card card-body mb-2">
                    <p class="mt-3">No results found for  {{ $group['title'] }}. Try different search terms.</p>
                </div>
                    
                @endif
            @endforeach
        @else
            <div class="no-results">
                <i class="fas fa-search fa-2x text-muted"></i>
                <p class="mt-3">No results found. Try different search terms.</p>
            </div>
        @endif
    </div>
@endsection
