@extends('layout.app')

@push('style')
    <style>
       :root {
            --proposal-primary: #50C878;
            --proposal-bg: var(--card-bg);
            --proposal-text: var(--body-color);
            --proposal-border: var(--border-color);
        }

        .backbg {
            background-color: var(--proposal-primary);
        }

        .backbg-primary {
            background-color: #137d37;
        }

        .proposal-container {
            max-width: 1140px;
            margin: 0 auto;
            background: var(--proposal-bg);
        }

        .proposal-header {
            position: relative;
            padding: 2.5rem;
            background: linear-gradient(45deg, #50C878, #7feca3);
            color: white;
        }

        .watermark {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 8rem;
            opacity: 0.1;
            font-weight: bold;
            color: white;
        }

        .status-badge {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            border-bottom: 2px solid var(--proposal-border);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            color: var(--proposal-text);
        }

        .highlight-box {
            background: var(--sidebar-diff-bg);
            border-radius: 0.5rem;
            padding: 1.5rem;
        }

        .timeline-item {
            padding-left: 2rem;
            border-left: 2px solid var(--proposal-border);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--primary-color);
        }

        .price-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .price-table th {
            background: var(--sidebar-diff-bg);
            padding: 1rem;
        }

        .price-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--proposal-border);
        }

        .signature-box {
            border: 1px dashed var(--proposal-border);
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid ">
        <div class="proposal-container shadow-lg rounded-lg overflow-hidden">
            <!-- Cover Page -->
            <div class="proposal-header">
                <div class="watermark">ESTIMATE</div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <span class="status-badge backbg-primary text-dark mb-3">
                                {{ $estimate?->_prefix }}-{{ $estimate?->_id }}
                            </span>
                            <h1 class="display-4 mb-2">{{ $estimate?->title }}</h1>
                            <p class="lead mb-0">Prepared for {{ $estimate?->typable?->title }}.
                                {{ $estimate?->typable?->first_name }} {{ $estimate?->typable?->last_name }}</p>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Prepared By</small>
                                <h4>{{ $estimate?->company?->name }}</h4>
                                <p class="mb-0">{{ $estimate?->company?->addresses?->street_address }}</p>
                                <p class="mb-0">{{ $estimate?->company?->addresses?->city?->name }},
                                    {{ $estimate?->company->addresses?->country?->name }}</p>
                                <p class="mb-0">{{ $estimate?->company?->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Valid Until</small>
                                <h4>{{ $estimate?->valid_date ? formatDateTime($estimate?->valid_date) : 'Not Specified' }}
                                </h4>
                                <p class="mb-0">Created:
                                    {{ formatDateTime($estimate?->creating_date) }}</p>


                                <p class="mb-3">Status: <span
                                        class="badge bg-{{ CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'][$estimate?->status] }} ms-2">{{ $estimate?->status }}</span>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-5">
                <!-- Executive Summary -->
                <section class="mb-5">

                    <div class="row">

                        @if (!empty($estimate?->product_details) && $estimate?->product_details != null)
                            @php
                                $details = json_decode($estimate->product_details, true);
                                $products = $details['products'] ?? [];
                                $additionalFields = $details['additional_fields'] ?? [];
                            @endphp

                            @if (!empty($products))
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
                                                                <small
                                                                    class="text-muted">{{ $product['description'] }}</small>
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
                                                                    floatval($product['qty']) *
                                                                        floatval($product['rate']);
                                                            },
                                                            0,
                                                        );

                                                        $totalTax = array_reduce(
                                                            $products,
                                                            function ($carry, $product) {
                                                                $amount =
                                                                    floatval($product['qty']) *
                                                                    floatval($product['rate']);
                                                                return $carry +
                                                                    ($amount * floatval($product['tax'])) / 100;
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
                                                            {{ number_format($total, 2) }}
                                                            {{ getSettingValue('Currency Code') }} </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if (!is_null(trim($estimate?->template?->template_details)) && $estimate?->template?->template_details != null)
                            <h2 class="section-title">Executive Summary</h2>
                            <p class="lead">
                                {!! $estimate?->template?->template_details !!}
                            </p>
                        @endif
                        @if (!is_null(trim($estimate?->details)) && $estimate?->details != null)
                            <h3 class="mt-3">Extra Details</h3>
                            <p>
                                {!! $estimate?->details !!}
                            </p>
                        @endif
                    </div>
                </section>



                <!-- Action Buttons -->

                <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                    <a href="{{ route('estimate.viewOpen', ['id' => $estimate->id]) }}"
                        class="dt-link btn btn-outline-dark me-2">
                        View as client
                    </a>
                    <button class="btn btn-outline-secondary me-2" onclick="printProposal()">
                        <i class="bi bi-download me-2"></i>Download PDF
                    </button>
                    @if ($estimate?->status !== 'ACCEPTED')
                        <button class="btn btn-primary" onclick="sendProposal('{{ $estimate?->id }}')">
                            <i class="bi bi-send me-2"></i>Send Estimate
                        </button>
                    @endif
                </div>


                @if ($estimate?->status === 'ACCEPTED')
                    <div class="container mt-4">
                        <div class="card shadow-sm">
                            <div class="card-header backbg text-white">
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
                                                                {{ $estimate?->accepted_details['first_name'] }}
                                                                {{ $estimate?->accepted_details['last_name'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Email:</td>
                                                            <td class="font-weight-bold">
                                                                {{ $estimate?->accepted_details['email'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-muted">Accepted On:</td>
                                                            <td class="font-weight-bold">

                                                                {{ formatDateTime($estimate?->accepted_details['accepted_at']) }}
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
                                                <img src="{{ $estimate?->accepted_details['signature'] }}"
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
    </div>
@endsection

@push('scripts')
    <script>
        function printProposal() {
            // Open print view in a new window

            let _id = "{{ $estimate->id }}";

            const printWindow = window.open(
                `/estimate/print/${_id}`,
                'PrintEstimate',
                'width=1140,height=800'
            );

            // Wait for content to load then print
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                    // Optional: Close the window after print dialog is closed
                    // printWindow.close();
                }, 500);
            };
        }

        function sendProposal($id) {
            // console.log($id)a
            let baseUrl =
                "{{ route(getPanelRoutes($module . '.sendEstimate'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', $id);

            console.log(url)
            $.ajax({
                url: url + '?api=true',
                method: "GET",
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    const successModal = new bootstrap.Modal(
                        document.getElementById("successModal")
                    );
                    $("#successModal .modal-body").text(
                        response.success
                    );
                    successModal.show();
                },
                error: function(xhr) {
                    const alertModal = new bootstrap.Modal(
                        document.getElementById("alertModal")
                    );
                    $("#alertModal .modal-body").text(
                        "An error occurred."
                    );
                    alertModal.show();
                }
            });
        }
    </script>
@endpush
