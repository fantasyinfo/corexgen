@extends('layout.app')

@push('style')
    <style>
        .search-table {
            width: 100%;
            border-collapse: collapse;
        }

        .search-table th, .search-table td {
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
    <div class="container">
        <h1 class="my-4">Search Results</h1>

        @if (!empty($data))
            @foreach ($data as $key => $group)
                <div class="card search-card">
                    <div class="card-header">
                        <h3>{{ $group['title'] }}</h3>
                    </div>
                    <div class="card-body">
                        @if (count($group['data']) > 0)
                            <table class="search-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @if ($key == 'companies')
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Actions</th>
                                        @elseif ($key == 'users')
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        @elseif ($key == 'plans')
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Offer Price</th>
                                            <th>Billing Cycle</th>
                      
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['data'] as $index => $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            @if ($key == 'companies')
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <td>
                                                    <a href="{{ route(getPanelRoutes($group['module'] . '.view'), ['id' => $item->id]) }}" class="btn btn-primary btn-sm">View</a>
                                                </td>
                                            @elseif ($key == 'users')
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->is_tenant ? 'Active' : 'Inactive' }}</td>
                                                <td>
                                                    <a href="{{ route(getPanelRoutes($group['module'] . '.view'), ['id' => $item->id]) }}" class="btn btn-primary btn-sm">View</a>
                                                </td>
                                            @elseif ($key == 'plans')
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->price }}</td>
                                                <td>{{ $item->offer_price }}</td>
                                                <td>{{ $item->billing_cycle }}</td>
                                               
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                           
                        @else
                            <p class="no-results">No results found in {{ $group['title'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="no-results">No results found.</div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        console.log('Search results loaded.');
    </script>
@endpush
