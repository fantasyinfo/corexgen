<!-- File: resources/views/proposals/print.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $estimate?->title }} - Print View</title>
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
        .proposal-container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Styles */
        .proposal-header {
            padding: 0 0 1.5cm 0;
            margin-bottom: 1cm;
            border-bottom: 1px solid #dee2e6;
        }

        .proposal-id {
            font-size: 10pt;
            color: #666;
            margin-bottom: 0.5cm;
        }

        .proposal-title {
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
    <div class="proposal-container">
        <!-- Header Section -->
        <div class="proposal-header">
            <div class="proposal-id">
                {{ $estimate?->_prefix }}-{{ $estimate?->_id }}
            </div>

            <h1 class="proposal-title">{{ $estimate?->title }}</h1>
            <p class="client-name">Prepared for {{ $estimate?->typable?->title }}.
                {{ $estimate?->typable?->first_name }} {{ $estimate?->typable?->last_name }}</p>

            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <div class="info-box-title">Prepared By</div>
                    <div class="info-box-content">
                        <h4 class="mb-2">{{ $estimate?->company?->name }}</h4>
                        <p class="mb-1">{{ $estimate?->company?->addresses?->street_address }}</p>
                        <p class="mb-1">{{ $estimate?->company?->addresses?->city?->name }},
                            {{ $estimate?->company->addresses?->country?->name }}</p>
                        <p class="mb-0">{{ $estimate?->company?->phone }}</p>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title">Estimate Details</div>
                    <div class="info-box-content">
                        <p class="mb-1">
                            <strong>Valid Until:</strong>
                            {{ $estimate?->valid_date ? formatDateTime($estimate?->valid_date) : 'Not Specified' }}
                        </p>
                        <p class="mb-0">
                            <strong>Created:</strong>
                            {{ formatDateTime($estimate?->creating_date) }}
                        </p>
                        <p class="mb-0">
                            <strong>Status:</strong>
                            <span class="status-badge">{{ $estimate?->status }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="proposal-content">
            @if (!empty($estimate?->product_details) && $estimate?->product_details != NULL)
            @php
                $details = json_decode($estimate->product_details, true);
                $products = $details['products'] ?? [];
                $additionalFields = $details['additional_fields'] ?? [];
            @endphp

            @if(!empty($products))
            <div class="card mb-4">
                <div class="card-header table-bg">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Estimate Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th class="text-center">Qty / Per Hr</th>
                                    <th class="text-end" width="200px;">Rate ({{ getSettingValue('Currency Symbol') }})</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end" width="200px;">Amount ({{ getSettingValue('Currency Symbol') }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    @php
                                        $qty = floatval($product['qty']);
                                        $rate = floatval($product['rate']);
                                        $tax = floatval($product['tax']);
                                        $amount = $qty * $rate;
                                        $taxAmount = ($amount * $tax) / 100;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $product['title'] }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $product['description'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">
                                                {{ number_format($qty) }} 
                                               
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            {{ getSettingValue('Currency Symbol') }} {{ number_format($rate, 2) }} {{ getSettingValue('Currency Code') }}
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted">
                                                {{ number_format($tax, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            {{ getSettingValue('Currency Symbol') }} {{ number_format($amount, 2) }} {{ getSettingValue('Currency Code') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-bg">
                                @php
                                    $subTotal = array_reduce(
                                        $products,
                                        function ($carry, $product) {
                                            return $carry +
                                                floatval($product['qty']) * floatval($product['rate']);
                                        },
                                        0,
                                    );

                                    $totalTax = array_reduce(
                                        $products,
                                        function ($carry, $product) {
                                            $amount =
                                                floatval($product['qty']) * floatval($product['rate']);
                                            return $carry + ($amount * floatval($product['tax'])) / 100;
                                        },
                                        0,
                                    );

                                    $discount = floatval($additionalFields['discount'] ?? 0);
                                    $discountAmount = ($subTotal * $discount) / 100;

                                    $adjustment = floatval($additionalFields['adjustment'] ?? 0);
                                    $total = $subTotal - $discountAmount + $totalTax + $adjustment;
                                @endphp

                                <tr>
                                    <td colspan="5" class="text-end">Sub Total:</td>
                                    <td class="text-end"> {{ getSettingValue('Currency Symbol') }} {{ number_format($subTotal, 2) }} {{ getSettingValue('Currency Code') }}</td>
                                </tr>
                                @if ($discount > 0)
                                    <tr>
                                        <td colspan="5" class="text-end text-danger">
                                            Discount ({{ number_format($discount, 1) }}%):
                                        </td>
                                        <td class="text-end text-danger">
                                            {{ getSettingValue('Currency Symbol') }}  -{{ number_format($discountAmount, 2) }} {{ getSettingValue('Currency Code') }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($totalTax > 0)
                                    <tr>
                                        <td colspan="5" class="text-end">Tax:</td>
                                        <td class="text-end"> {{ getSettingValue('Currency Symbol') }} {{ number_format($totalTax, 2) }} {{ getSettingValue('Currency Code') }}</td>
                                    </tr>
                                @endif
                                @if ($adjustment != 0)
                                    <tr>
                                        <td colspan="5"
                                            class="text-end {{ $adjustment < 0 ? 'text-danger' : 'text-success' }}">
                                            Adjustment:
                                        </td>
                                        <td
                                            class="text-end {{ $adjustment < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ getSettingValue('Currency Symbol') }}  {{ $adjustment > 0 ? '+' : '' }}{{ number_format($adjustment, 2) }} {{ getSettingValue('Currency Code') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end">Total:</td>
                                    <td class="text-end"> {{ getSettingValue('Currency Symbol') }} {{ number_format($total, 2) }} {{ getSettingValue('Currency Code') }} </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        @endif
            <section>
                <h2 class="section-title">Executive Summary</h2>
                <div class="proposal-body">
                    {!! $estimate?->template?->template_details !!}

                    @if (!is_null(trim($estimate?->details)))
                        <h3 class="mt-4 mb-3">Additional Details</h3>
                        <div class="additional-details">
                            {!! $estimate?->details !!}
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="proposal-content">
        @if ($estimate->status === 'ACCEPTED')
            <div class="container mt-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Estimate Acceptance Details</h4>
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
                                                        {{ $estimate->accepted_details['first_name'] }}
                                                        {{ $estimate->accepted_details['last_name'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Email:</td>
                                                    <td class="font-weight-bold">
                                                        {{ $estimate->accepted_details['email'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">Accepted On:</td>
                                                    <td class="font-weight-bold">
                                                        {{ formatDateTime($estimate->accepted_at) }}
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
                                        <img src="{{ $estimate->accepted_details['signature'] }}"
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
    </div>
</body>

</html>
