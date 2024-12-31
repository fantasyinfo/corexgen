@extends('layout.app')

@push('style')
    <style>
        :root {
            --proposal-primary: var(--primary-color);
            --proposal-bg: var(--card-bg);
            --proposal-text: var(--body-color);
            --proposal-border: var(--border-color);
        }

        .proposal-container {
            max-width: 1140px;
            margin: 0 auto;
            background: var(--proposal-bg);
        }

        .proposal-header {
            position: relative;
            padding: 2.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-hover));
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
    <div class="container-fluid py-5">
        <div class="proposal-container shadow-lg rounded-lg overflow-hidden">
            <!-- Cover Page -->
            <div class="proposal-header">
                <div class="watermark">PROPOSAL</div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <span class="status-badge bg-secondary text-dark mb-3">
                                {{ $proposal->_prefix }}{{ $proposal->_id }}
                            </span>
                            <h1 class="display-4 mb-2">{{ $proposal->title }}</h1>
                            <p class="lead mb-0">Prepared for {{ $proposal->typable->title }}.
                                {{ $proposal->typable->first_name }} {{ $proposal->typable->last_name }}</p>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Prepared By</small>
                                <h4>{{ $proposal->company->name }}</h4>
                                <p class="mb-0">{{ $proposal->company->addresses->street_address }}</p>
                                <p class="mb-0">{{ $proposal->company->addresses->city->name }},
                                    {{ $proposal->company->addresses->country->name }}</p>
                                <p class="mb-0">{{ $proposal->company->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Valid Until</small>
                                <h4>{{ $proposal->valid_date ? \Carbon\Carbon::parse($proposal->valid_date)->format('F d, Y') : 'Not Specified' }}
                                </h4>
                                <p class="mb-0">Created:
                                    {{ \Carbon\Carbon::parse($proposal->creating_date)->format('F d, Y') }}</p>
                                <p class="mb-3">Status: <span
                                        class="badge bg-{{ CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'][$proposal->status] }} ms-2">{{ $proposal->status }}</span>
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
                        @if (!is_null(trim($proposal?->template?->template_details)))
                            <h2 class="section-title">Executive Summary</h2>
                            <p class="lead">
                                {!! $proposal?->template?->template_details !!}
                            </p>
                        @endif
                        @if (!is_null(trim($proposal?->details)))
                            <h3 class="mt-3">Extra Details</h3>
                            <p>
                                {!! $proposal?->details !!}
                            </p>
                        @endif
                    </div>
                </section>



                <!-- Action Buttons -->
                @if ($proposal->status !== 'ACCEPTED')
                    <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                        <button class="btn btn-outline-secondary me-2" onclick="printProposal()">
                            <i class="bi bi-download me-2"></i>Download PDF
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Proposal
                        </button>
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

            let _id = "{{ $proposal->id }}";
            let baseUrl = "{{ route(getPanelRoutes($module . '.print'), ['id' => ':id']) }}";
            let url = baseUrl.replace(':id', _id);


            const printWindow = window.open(
                url,
                'PrintProposal',
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
    </script>
@endpush
