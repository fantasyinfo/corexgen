@extends('layout.app')

@push('style')
    <style>
        :root {
            --proposal-primary: #333333;
            --proposal-bg: var(--card-bg);
            --proposal-text: var(--body-color);
            --proposal-border: var(--border-color);
        }

        .backbg {
            background-color: var(--proposal-primary);
        }

        .backbg-primary {
            background-color: #161515;
        }

        .proposal-container {
            max-width: 1140px;
            margin: 0 auto;
            background: var(--proposal-bg);
        }

        .proposal-header {
            position: relative;
            padding: 2.5rem;
            background: linear-gradient(45deg, #333333, #5c5c5c);
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
                <div class="watermark">CONTRACT</div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <span class="status-badge  backbg-primary text-dark mb-3">
                                {{ $contract?->_prefix }}-{{ $contract?->_id }}
                            </span>
                            <h1 class="display-4 mb-2">{{ $contract?->title }}</h1>
                            <p class="lead mb-0">Prepared for {{ $contract?->typable?->title }}.
                                {{ $contract?->typable?->first_name }} {{ $contract?->typable?->last_name }}</p>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Prepared By</small>
                                <h4>{{ $contract?->company?->name }}</h4>
                                <p class="mb-0">{{ $contract?->company?->addresses?->street_address }}</p>
                                <p class="mb-0">{{ $contract?->company?->addresses?->city?->name }},
                                    {{ $contract?->company->addresses?->country?->name }}</p>
                                <p class="mb-0">{{ $contract?->company?->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Valid Until</small>
                                <h4>{{ $contract?->valid_date ? formatDateTime($contract?->valid_date) : 'Not Specified' }}
                                </h4>
                                <p class="mb-0">Created:
                                    {{ formatDateTime($contract?->creating_date) }}</p>


                                <p class="mb-3">Status: <span
                                        class="badge bg-{{ CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'][$contract?->status] }} ms-2">{{ $contract?->status }}</span>
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


                        @if (!is_null(trim($contract?->template?->template_details)) && $contract?->template?->template_details != null)
                            <h2 class="section-title">Executive Summary</h2>
                            <p class="lead">
                                {!! $contract?->template?->template_details !!}
                            </p>
                        @endif
                        @if (!is_null(trim($contract?->details)) && $contract?->details != null)
                            <h3 class="mt-3">Extra Details</h3>
                            <p>
                                {!! $contract?->details !!}
                            </p>
                        @endif
                    </div>
                </section>



                <!-- Action Buttons -->

                <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                    <a href="{{ route('contract.viewOpen', ['id' => $contract->uuid]) }}"
                        class="dt-link btn btn-outline-dark me-2">
                        View as client
                    </a>
                    <button class="btn btn-outline-secondary me-2" onclick="printProposal()">
                        <i class="bi bi-download me-2"></i>Download PDF
                    </button>
                    @if ($contract?->status !== 'ACCEPTED')
                        <button class="btn btn-primary" onclick="sendProposal('{{ $contract?->id }}')">
                            <i class="bi bi-send me-2"></i>Send Contract
                        </button>
                    @endif
                </div>


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
                                                                    {{ $contract?->company_accepted_details['first_name'] ?? '' }}
                                                                    {{ $contract?->company_accepted_details['last_name'] ?? '' }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Email:</td>
                                                                <td class="font-weight-bold">
                                                                    {{ $contract?->company_accepted_details['email'] ?? ''}}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Accepted On:</td>
                                                                <td class="font-weight-bold">
                                                                    {{ formatDateTime($contract?->company_accepted_details['accepted_at'] ?? now()) }}
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
                                                                    {{ $contract->accepted_details['first_name'] ?? $contract?->typable?->first_name }}
                                                                    {{ $contract->accepted_details['last_name'] ?? $contract?->typable?->last_name}}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Email:</td>
                                                                <td class="font-weight-bold">
                                                                    {{ $contract->accepted_details['email'] ?? "" }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-muted">Accepted On:</td>
                                                                <td class="font-weight-bold">
                                                                    {{ formatDateTime($contract->accepted_details['accepted_at'] ?? now())  }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <h5 class="text-muted mb-3">Digital Signature</h5>
                                                <div class="border rounded p-3 bg-light">
                                                    <img src="{{ $contract->accepted_details['signature']  ?? 'Direct Signed via Panel'}}"
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
    </div>
@endsection

@push('scripts')
    <script>
        function printProposal() {
            // Open print view in a new window

            let _id = "{{ $contract->id }}";

            const printWindow = window.open(
                `/contract/print/${_id}`,
                'PrintContract',
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
                "{{ route(getPanelRoutes($module . '.sendContract'), ['id' => ':id']) }}";
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
