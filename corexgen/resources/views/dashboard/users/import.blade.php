@extends('layout.app')
@push('style')
    <style>
        /* Styling specific to bulk import sample table */
        #bulk-import-sample {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            /* margin: 1.5rem 0; */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* border: 2px solid #e2e8f0; */
        }

        #bulk-import-sample thead {
            background: var(--primary-color);
        }

        #bulk-import-sample thead th {
            color: white;
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border: none;
            white-space: nowrap;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        #bulk-import-sample tbody tr {
            transition: all 0.2s ease;
            background-color: white;
        }

        #bulk-import-sample tbody tr:nth-child(even) {
            /* background-color: #f8fafc; */
        }

        #bulk-import-sample tbody tr:hover {
            /* background-color: #f1f5f9; */
            transform: scale(1.001);
        }

        #bulk-import-sample td {
            padding: 1rem;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.5;
            white-space: nowrap;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #bulk-import-sample {
                font-size: 0.9rem;
            }

            #bulk-import-sample td,
            #bulk-import-sample th {
                padding: 0.75rem;
            }
        }

        /* Container styling */
        .table-responsive {
            border-radius: 8px;
            /* background: white; */
            padding: 1rem;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">
        <div class="">
            @include('layout.components.header-buttons')

            <div class="shadow-sm rounded">

                @include('layout.components.bulk-import-modal')


                @if (hasPermission('USERS.IMPORT'))
                    <h5>{{ __('Bulk Import Users Sample CSV Format') }}</h5>



                    <div class="action-buttons my-4">
                        <style>
                            .action-buttons {
                                display: flex;
                                gap: 1rem;
                                flex-wrap: wrap;
                                align-items: center;
                            }

                            .action-buttons .btn {
                                display: inline-flex;
                                align-items: center;
                                white-space: nowrap;
                            }

                            @media (max-width: 768px) {
                                .action-buttons {
                                    flex-direction: column;
                                    align-items: stretch;
                                }

                                .action-buttons .btn {
                                    width: 100%;
                                    justify-content: center;
                                }
                            }
                        </style>

                        <!-- Primary action first -->
                        <a class="btn btn-primary btn-xl" href="#" data-bs-toggle="modal"
                            data-bs-target="#bulkImportModal" title="{{ __('crud.Import') }}">
                            <i class="fas fa-upload me-2"></i> {{ __('crud.Import Users Lists') }}
                        </a>

                        <!-- Secondary actions -->
                        <a class="btn btn-outline-primary btn-xl" href="/importcsv/users.csv" target="_blank"
                            title="{{ __('crud.Download Sample Csv') }}">
                            <i class="fas fa-download me-2"></i> {{ __('crud.Download Sample Csv') }}
                        </a>

                        <a href="{{ route('download.countries') }}" class="btn btn-outline-primary btn-xl">
                            <i class="fas fa-download me-2"></i> {{ __('crud.Download Countries List for Country ID') }}
                        </a>

                    </div>

                    <div class="table-responsive">
                        <table id="bulk-import-sample" class="table table-striped ">
                            <thead>
                                <tr>
                                    @foreach ($headers as $key => $value)
                                        <th>{{ $value['key'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $row)
                                    <tr>
                                        @foreach ($headers as $key => $value)
                                            <td>{{ $row[$value['key']] ?? 'N/A' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>

                    <div class="mt-4">
                        <h3>Instructions:</h3>
                        <ul>
                            @foreach ($headers as $key => $value)
                                <li><strong>{{ $value['key'] }}</strong>: {{ $value['message'] }}</li>
                            @endforeach
                        </ul>

                    </div>
                @else
                    <div class="no-data-found">
                        <i class="fas fa-ban"></i>
                        <span class="mx-2">{{ __('crud.You do not have permission to view the table') }}</span>
                    </div>
                @endif





            </div>
        </div>
    </div>
@endsection
@include('layout.components.bulk-import-js')
@push('scripts')
@endpush
