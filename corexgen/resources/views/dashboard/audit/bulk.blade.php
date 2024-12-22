@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="shadow-sm rounded">
            @if (hasPermission('BULK_IMPORT_STATUS.READ_ALL') || hasPermission('BULK_IMPORT_STATUS.READ'))
                @php
                    $columns = [
                        [
                            'data' => 'file_name',
                            'name' => 'file_name',
                            'label' => __('Import File'),
                            'searchable' => true,
                            'orderable' => true,
                            'width' => '200px',
                        ],
                        [
                            'data' => 'import_type',
                            'name' => 'import_type',
                            'label' => __('Type'),
                            'searchable' => true,
                            'orderable' => true,
                            'width' => '100px',
                        ],
                        [
                            'data' => 'progress',
                            'name' => 'progress',
                            'label' => __('Progress'),
                            'searchable' => false,
                            'orderable' => false,
                            'width' => '150px',
                        ],
                        [
                            'data' => 'status',
                            'name' => 'status',
                            'label' => __('Status'),
                            'searchable' => true,
                            'orderable' => true,
                            'width' => '100px',
                        ],
                        [
                            'data' => 'actions',
                            'name' => 'actions',
                            'label' => __('Actions'),
                            'searchable' => false,
                            'orderable' => false,
                            'width' => '100px',
                        ],
                    ];
                @endphp

                <div class="table-responsive  table-bg">
                    <table id="importHistoryTable" class="table table-striped table-bordered ui celled">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th>{{ $column['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- Error Details Modal -->
                <div class="modal fade" id="errorDetailsModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Import Error Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="import-summary mb-4">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Total Rows:</strong> <span id="totalRows"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Successful:</strong> <span id="successfulRows"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Failed:</strong> <span id="failedRows"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Status:</strong> <span id="importStatus"></span>
                                        </div>
                                    </div>
                                </div>
                                <div id="errorList" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="no-data-found">
                    <i class="fas fa-ban"></i>
                    <span class="mx-2">{{ __('crud.You do not have permission to view the table') }}</span>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#importHistoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route(getPanelRoutes($module . '.bulkimport')) }}',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                },
                columns: [{
                        data: 'file_name',
                        render: function(data) {
                            return `<span title="${data}">${data.split('_').pop()}</span>`;
                        }
                    },
                    {
                        data: 'import_type'
                    },
                    {
                        data: null,
                        render: function(data) {
                            return formatProgress(data);
                        }
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            return formatStatus(data);
                        }
                    },
                    {
                        data: null,
                        render: function(data) {
                            if (data.failed_rows > 0) {
                                // Properly escape the data for the onclick attribute
                                const escapedData = encodeURIComponent(JSON.stringify(data));
                                return `<button class="btn btn-sm btn-danger" 
                                onclick="showErrorDetails('${escapedData}')">
                                View Errors
                            </button>`;
                            }
                            return '';
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                responsive: true
            });
        });

        // Update the showErrorDetails function to handle the encoded data
        function showErrorDetails(encodedData) {
            try {
                const data = JSON.parse(decodeURIComponent(encodedData));

                // Update summary information
                $('#totalRows').text(data.total_rows);
                $('#successfulRows').text(data.successful_rows);
                $('#failedRows').text(data.failed_rows);
                $('#importStatus').html(formatStatus(data.status));

                // Parse and display error details
                const errorList = $('#errorList');
                errorList.empty();

                if (data.failed_rows_details) {
                    let errors;
                    try {
                        // Handle potential HTML entities in the JSON string
                        const decodedDetails = data.failed_rows_details
                            .replace(/&quot;/g, '"')
                            .replace(/&#039;/g, "'")
                            .replace(/&amp;/g, "&");
                        errors = JSON.parse(decodedDetails);

                        errors.forEach(error => {
                            const errorCard = `
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                Row ${error.row}
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    ${error.errors.map(err => `<li>• ${err}</li>`).join('')}
                                </ul>
                            </div>
                        </div>`;
                            errorList.append(errorCard);
                        });
                    } catch (parseError) {
                        errorList.html(
                        `<div class="alert alert-danger">Error parsing details: ${parseError.message}</div>`);
                    }
                } else {
                    errorList.html('<div class="alert alert-info">No detailed error information available.</div>');
                }

                $('#errorDetailsModal').modal('show');
            } catch (error) {
                console.error('Error processing import details:', error);
                alert('Error showing import details. Please try again.');
            }
        }

        // Helper function for formatting progress bar
        function formatProgress(data) {
            const percentage = data.total_rows > 0 ?
                (data.successful_rows / data.total_rows) * 100 : 0;

            return `
        <div class="progress" style="height: 20px;">
            <div class="progress-bar ${data.status === 'failed' ? 'bg-danger' : 'bg-success'}" 
                 role="progressbar" 
                 style="width: ${percentage}%">
                ${data.successful_rows}/${data.total_rows}
            </div>
        </div>`;
        }

        // Helper function for formatting status badges
        function formatStatus(status) {
            const statusClasses = {
                'pending': 'bg-warning',
                'processing': 'bg-info',
                'completed': 'bg-success',
                'failed': 'bg-danger'
            };

            return `<span class="badge ${statusClasses[status] || 'bg-secondary'}">${status.toUpperCase()}</span>`;
        }
    </script>
@endpush

@push('style')
    <style>
        .progress {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .import-summary {
            /* background-color: #f8f9fa; */
            padding: 15px;
            border-radius: 5px;
        }

        #errorList .card-header {
            padding: 0.5rem 1rem;
        }

        #errorList .card-body {
            padding: 1rem;
        }

        #errorList ul li {
            margin-bottom: 0.5rem;
        }

        #errorList ul li:last-child {
            margin-bottom: 0;
        }
    </style>
@endpush
