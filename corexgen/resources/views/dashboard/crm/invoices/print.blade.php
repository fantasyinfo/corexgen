<!-- File: resources/views/proposals/print.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoice?->title }} - Print View</title>
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
        .invoice-container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Styles */
        .invoice-header {
            padding: 0 0 1.5cm 0;
            margin-bottom: 1cm;
            border-bottom: 1px solid #dee2e6;
        }

        .invoice-id {
            font-size: 10pt;
            color: #666;
            margin-bottom: 0.5cm;
        }

        .invoice-title {
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
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="invoice-id">
                {{ $invoice?->_prefix }}-{{ $invoice?->_id }}
            </div>

            <h1 class="display-4 mb-2">{{ $invoice?->task?->title }}</h1>
            <p class="lead mb-0">Prepared for Client.
                {{ $invoice?->client?->first_name }} {{ $invoice?->client?->last_name }}</p>

            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <div class="info-box-title">Prepared By</div>
                    <div class="info-box-content">
                        <h4 class="mb-2">{{ $invoice?->company?->name }}</h4>
                        <p class="mb-1">{{ $invoice?->company?->addresses?->street_address }}</p>
                        <p class="mb-1">{{ $invoice?->company?->addresses?->city?->name }},
                            {{ $invoice?->company->addresses?->country?->name }}</p>
                        <p class="mb-0">{{ $invoice?->company?->phone }}</p>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-box-title">Invoice Details</div>
                    <div class="info-box-content">
                        <p class="mb-1">
                            <strong>Due Date:</strong>
                            {{ $invoice?->due_date ? formatDateTime($invoice?->due_date) : 'Not Specified' }}
                        </p>
                        <p class="mb-0">
                            <strong>Created:</strong>
                            {{ formatDateTime($invoice?->issue_date) }}
                        </p>
                        <p class="mb-0">
                            <strong>Status:</strong>
                            <span class="status-badge">{{ $invoice?->status }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="invoice-content">
            @if (!empty($invoice?->product_details) && $invoice?->product_details != null)
                @php
                    $details = json_decode($invoice->product_details, true);
                    $products = $details['products'] ?? [];
                    $additionalFields = $details['additional_fields'] ?? [];
                @endphp

                @if (!empty($products))
                    <div class="card mb-4">
                        <div class="card-header table-bg">
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice me-2"></i>
                                Proposal Details
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
                                            <th class="text-end" width="200px;">Rate
                                                ({{ getSettingValue('Currency Symbol') }})</th>
                                            <th class="text-end">Tax</th>
                                            <th class="text-end" width="200px;">Amount
                                                ({{ getSettingValue('Currency Symbol') }})</th>
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
                                                    {{ getSettingValue('Currency Symbol') }}
                                                    {{ number_format($rate, 2) }}
                                                    {{ getSettingValue('Currency Code') }}
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-muted">
                                                        {{ number_format($tax, 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    {{ getSettingValue('Currency Symbol') }}
                                                    {{ number_format($amount, 2) }}
                                                    {{ getSettingValue('Currency Code') }}
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
                                                    $amount = floatval($product['qty']) * floatval($product['rate']);
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
                                            <td class="text-end"> {{ getSettingValue('Currency Symbol') }}
                                                {{ number_format($subTotal, 2) }}
                                                {{ getSettingValue('Currency Code') }}</td>
                                        </tr>
                                        @if ($discount > 0)
                                            <tr>
                                                <td colspan="5" class="text-end text-danger">
                                                    Discount ({{ number_format($discount, 1) }}%):
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ getSettingValue('Currency Symbol') }}
                                                    -{{ number_format($discountAmount, 2) }}
                                                    {{ getSettingValue('Currency Code') }}
                                                </td>
                                            </tr>
                                        @endif
                                        @if ($totalTax > 0)
                                            <tr>
                                                <td colspan="5" class="text-end">Tax:</td>
                                                <td class="text-end"> {{ getSettingValue('Currency Symbol') }}
                                                    {{ number_format($totalTax, 2) }}
                                                    {{ getSettingValue('Currency Code') }}</td>
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
                                                    {{ getSettingValue('Currency Symbol') }}
                                                    {{ $adjustment > 0 ? '+' : '' }}{{ number_format($adjustment, 2) }}
                                                    {{ getSettingValue('Currency Code') }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="fw-bold">
                                            <td colspan="5" class="text-end">Total:</td>
                                            <td class="text-end"> {{ getSettingValue('Currency Symbol') }}
                                                {{ number_format($total, 2) }} {{ getSettingValue('Currency Code') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            <section>
                <h2 class="section-title">Notes</h2>
                <div class="invoice-body">


                    @if (!is_null(trim($invoice?->notes)))
                        <h3 class="mt-4 mb-3">Notes</h3>
                        <div class="additional-details">
                            {!! $invoice?->notes !!}
                        </div>
                    @endif
                </div>
            </section>
        </div>


    </div>
</body>

</html>
