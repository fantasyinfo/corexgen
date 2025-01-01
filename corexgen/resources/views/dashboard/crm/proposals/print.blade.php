<!-- File: resources/views/proposals/print.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proposal?->title }} - Print View</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }

        /* Print-specific styles */
        .proposal-container {
            max-width: 1140px;
            margin: 0 auto;
            background: white;
        }

        .proposal-header {
            padding: 3rem;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .proposal-id {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #e9ecef;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .proposal-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: #212529;
        }

        .client-name {
            font-size: 1.25rem;
            color: #495057;
        }

        .info-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 1rem;
            margin-top: 2rem;
        }

        .info-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }

        .info-box-title {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .proposal-content {
            padding: 3rem;
        }

        .section-title {
            font-size: 1.75rem;
            color: #212529;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        /* Status styles */
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Page break utilities */
        .page-break {
            page-break-before: always;
        }

        @page {
            margin: 0.5cm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="proposal-container">
        <!-- Header Section -->
        <div class="proposal-header">
            <div class="proposal-id">
                {{ $proposal?->_prefix }}{{ $proposal?->_id }}
            </div>

            <h1 class="proposal-title">{{ $proposal?->title }}</h1>
            <p class="client-name">Prepared for {{ $proposal?->typable?->title }}.
                {{ $proposal?->typable?->first_name }} {{ $proposal?->typable?->last_name }}</p>

            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <div class="info-box-title">Prepared By</div>
                    <div class="info-box-content">
                        <h4 class="mb-2">{{ $proposal?->company?->name }}</h4>
                        <p class="mb-1">{{ $proposal?->company?->addresses?->street_address }}</p>
                        <p class="mb-1">{{ $proposal?->company?->addresses?->city?->name }},
                            {{ $proposal?->company->addresses?->country?->name }}</p>
                        <p class="mb-0">{{ $proposal?->company?->phone }}</p>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title">Proposal Details</div>
                    <div class="info-box-content">
                        <p class="mb-1">
                            <strong>Valid Until:</strong>
                            {{ $proposal?->valid_date ? \Carbon\Carbon::parse($proposal?->valid_date)->format('F d, Y') : 'Not Specified' }}
                        </p>
                        <p class="mb-0">
                            <strong>Created:</strong>
                            {{ \Carbon\Carbon::parse($proposal?->creating_date)->format('F d, Y') }}
                        </p>
                        <p class="mb-0">
                            <strong>Status:</strong>
                            <span class="status-badge">{{ $proposal?->status }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="proposal-content">
            <section>
                <h2 class="section-title">Executive Summary</h2>
                <div class="proposal-body">
                    {!! $proposal?->template?->template_details !!}

                    @if (!is_null(trim($proposal?->details)))
                        <h3 class="mt-4 mb-3">Additional Details</h3>
                        <div class="additional-details">
                            {!! $proposal?->details !!}
                        </div>
                    @endif
                </div>
            </section>
        </div>

        @if ($proposal->status === 'ACCEPTED')
            <div class="container mt-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Proposal Acceptance Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="text-muted mb-3">Client Information</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td class="text-muted" style="width: 140px;">Name:</td>
                                                    <td class="font-weight-bold">
                                                        {{ $proposal->accepted_details['first_name'] }}
                                                        {{ $proposal->accepted_details['last_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Email:</td>
                                                    <td class="font-weight-bold">
                                                        {{ $proposal->accepted_details['email'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Accepted On:</td>
                                                    <td class="font-weight-bold">
                                                        {{ \Carbon\Carbon::parse($proposal->accepted_at)->format('M d, Y h:i A') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5 class="text-muted mb-3">Digital Signature</h5>
                                    <div class="border rounded p-3 bg-light">
                                        <img src="{{ $proposal->accepted_details['signature'] }}"
                                            alt="Digital Signature" class="img-fluid" style="max-height: 150px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <div>
                                    This proposal has been officially accepted and signed by the client.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>
