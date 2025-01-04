<!-- File: resources/views/Contracts/print.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract?->title }} - Print View</title>
    <style>
        /* Reset and Base Styles */
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
            font-size: 12pt;
        }

        /* A4 Print Optimization */
        @page {
            size: A4;
            margin: 2cm 1.5cm;
        }

        /* Container */
        .Contract-container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Styles */
        .Contract-header {
            padding: 0 0 1.5cm 0;
            margin-bottom: 1cm;
            border-bottom: 1px solid #dee2e6;
        }

        .Contract-id {
            font-size: 10pt;
            color: #666;
            margin-bottom: 0.5cm;
        }

        .Contract-title {
            font-size: 24pt;
            font-weight: 700;
            margin-bottom: 0.5cm;
            color: #000;
        }

        .client-name {
            font-size: 14pt;
            color: #333;
            margin-bottom: 1cm;
        }

        /* Info Section Grid */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1cm;
            margin-bottom: 1.5cm;
        }

        .info-box {
            padding: 0.5cm;
            border: 1px solid #dee2e6;
            page-break-inside: avoid;
        }

        .info-box-title {
            font-size: 10pt;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 0.3cm;
            font-weight: bold;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1cm 0;
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6;
            padding: 0.3cm;
            text-align: left;
            font-size: 11pt;
            color: #333;
        }

        td {
            padding: 0.3cm;
            border-bottom: 1px solid #eee;
            font-size: 11pt;
        }

        /* Amount Columns */
        .text-end {
            text-align: right;
        }

        /* Summary Section */
        tfoot tr {
            background-color: #f8f9fa !important;
            font-weight: bold;
        }

        tfoot td {
            padding: 0.3cm;
            border-top: 2px solid #dee2e6;
        }

        /* Signature Section */
        .acceptance-details {
            margin-top: 2cm;
            page-break-before: always;
            page-break-inside: avoid;
        }

        .signature-container {
            margin-top: 1cm;
            border: 1px solid #dee2e6;
            padding: 0.5cm;
            background-color: #fff;
        }

        .signature-image {
            max-height: 4cm;
            width: auto;
            display: block;
        }

        /* Executive Summary */
        .section-title {
            font-size: 18pt;
            color: #000;
            margin-bottom: 0.8cm;
            padding-bottom: 0.3cm;
            border-bottom: 1px solid #dee2e6;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.2cm 0.4cm;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            font-size: 10pt;
        }

        /* Print Optimizations */
        @media print {
            .no-print {
                display: none !important;
            }

            a {
                text-decoration: none;
                color: #000;
            }

            .table-responsive {
                overflow-x: visible !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header {
                background-color: transparent !important;
                border-bottom: 2px solid #dee2e6 !important;
                padding: 0.5cm 0 !important;
            }

            .alert {
                border: 1px solid #28a745 !important;
                padding: 0.5cm !important;
                margin: 0.5cm 0 !important;
            }
        }
    </style>
</head>

<body>
    <div class="Contract-container">
        <!-- Header Section -->
        <div class="Contract-header">
            <div class="Contract-id">
                {{ $contract?->_prefix }}-{{ $contract?->_id }}
            </div>

            <h1 class="Contract-title">{{ $contract?->title }}</h1>
            <p class="client-name">Prepared for {{ $contract?->typable?->title }}.
                {{ $contract?->typable?->first_name }} {{ $contract?->typable?->last_name }}</p>

            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <div class="info-box-title">Prepared By</div>
                    <div class="info-box-content">
                        <h4 class="mb-2">{{ $contract?->company?->name }}</h4>
                        <p class="mb-1">{{ $contract?->company?->addresses?->street_address }}</p>
                        <p class="mb-1">{{ $contract?->company?->addresses?->city?->name }},
                            {{ $contract?->company->addresses?->country?->name }}</p>
                        <p class="mb-0">{{ $contract?->company?->phone }}</p>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title">Contract Details</div>
                    <div class="info-box-content">
                        <p class="mb-1">
                            <strong>Valid Until:</strong>
                            {{ $contract?->valid_date ? \Carbon\Carbon::parse($contract?->valid_date)->format('F d, Y') : 'Not Specified' }}
                        </p>
                        <p class="mb-0">
                            <strong>Created:</strong>
                            {{ \Carbon\Carbon::parse($contract?->creating_date)->format('F d, Y') }}
                        </p>
                        <p class="mb-0">
                            <strong>Status:</strong>
                            <span class="status-badge">{{ $contract?->status }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="Contract-content">
           
            <section>
                <h2 class="section-title">Executive Summary</h2>
                <div class="Contract-body">
                    {!! $contract?->template?->template_details !!}

                    @if (!is_null(trim($contract?->details)))
                        <h3 class="mt-4 mb-3">Additional Details</h3>
                        <div class="additional-details">
                            {!! $contract?->details !!}
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="Contract-content">
            @if ($contract?->status === 'ACCEPTED' || $contract?->statusCompany == true)
            <div class="container mt-4">
                <div class="card shadow-sm">
                    <div class="card-header backbg text-white">
                        <h4 class="mb-0">Contract Acceptance Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if ($contract?->statusCompany == true)
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h5 class="text-muted mb-3">Company Information</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 140px;">Name:</td>
                                                        <td class="font-weight-bold">
                                                            {{ $contract?->company_accepted_details['first_name'] }}
                                                            {{ $contract?->company_accepted_details['last_name'] }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Email:</td>
                                                        <td class="font-weight-bold">
                                                            {{ $contract?->company_accepted_details['email'] }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Accepted On:</td>
                                                        <td class="font-weight-bold">
                                                            {{ \Carbon\Carbon::parse($contract?->company_accepted_details['accepted_at'])->format('M d, Y h:i A') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h5 class="text-muted mb-3">Digital Signature</h5>
                                        <div class="border rounded p-3 bg-light">
                                            <img src="{{ $contract?->company_accepted_details['signature'] }}"
                                                alt="Digital Signature" class="img-fluid"
                                                style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($contract?->status === 'ACCEPTED')
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h5 class="text-muted mb-3">Client Information</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted" style="width: 140px;">Name:</td>
                                                        <td class="font-weight-bold">
                                                            {{ $contract->accepted_details['first_name'] }}
                                                            {{ $contract->accepted_details['last_name'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Email:</td>
                                                        <td class="font-weight-bold">
                                                            {{ $contract->accepted_details['email'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Accepted On:</td>
                                                        <td class="font-weight-bold">
                                                            {{ \Carbon\Carbon::parse($contract->accepted_details['accepted_at'])->format('M d, Y h:i A') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <h5 class="text-muted mb-3">Digital Signature</h5>
                                        <div class="border rounded p-3 bg-light">
                                            <img src="{{ $contract->accepted_details['signature'] }}"
                                                alt="Digital Signature" class="img-fluid"
                                                style="max-height: 150px;">
                                        </div>
                                    </div>
                                </div>
                            @endif
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
    </div>
</body>

</html>
