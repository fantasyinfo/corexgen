@extends('layout.guest')

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

                <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                    <button class="btn btn-outline-secondary me-2" onclick="printProposal()">
                        <i class="fas fa-down me-2"></i>Download PDF
                    </button>
                    @if ($proposal->status !== 'ACCEPTED')
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#signedModal">
                            <i class="fas fa-check me-2"></i>Accept Proposal
                        </button>
                    @endif
                </div>


                @if ($proposal->status !== 'ACCEPTED')
                    <div class="modal fade" id="signedModal" tabindex="-1" aria-labelledby="signedModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered ">
                            <form action="{{ route('proposal.accept') }}" method="POST" id="acceptProposalForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $proposal->id }}" />
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="signedModalLabel">Accept This Proposal</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">First Name</label>
                                                <input type="text" id="first_name" name="first_name" class="form-control"
                                                    placeholder="Enter your first name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control"
                                                    placeholder="Enter your last name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="client_email" class="form-label">Your Email</label>
                                                <input type="email" id="client_email" name="email" class="form-control"
                                                    placeholder="Enter your email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="client_signature" class="form-label">Digital Signature</label>
                                                <div>
                                                    <canvas id="signaturePad" class="border" width="400px"
                                                        height="200"></canvas>
                                                </div>

                                                <input type="hidden" id="client_signature" name="signature" required>
                                                <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                    onclick="clearSignature()">Clear Signature</button>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Accept Proposal</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

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
                                                                {{ \Carbon\Carbon::parse($proposal->accepted_details['accepted_at'])->format('M d, Y h:i A') }}
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printProposal() {
            // Open print view in a new window

            let _id = "{{ $proposal->id }}";

            const printWindow = window.open(
                `/proposal/print/${_id}`,
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

        // Initialize signature pad when document is loaded
        let canvas;
        let context;
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        document.addEventListener('DOMContentLoaded', function() {
            // Get canvas element
            canvas = document.getElementById('signaturePad');
            context = canvas.getContext('2d');

            // Set canvas styling
            context.strokeStyle = '#000000';
            context.lineWidth = 2;
            context.lineCap = 'round';

            // Add event listeners for mouse/touch events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Touch events for mobile devices
            canvas.addEventListener('touchstart', handleTouchStart);
            canvas.addEventListener('touchmove', handleTouchMove);
            canvas.addEventListener('touchend', stopDrawing);

            // Handle form submission
            const form = document.getElementById('acceptProposalForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (isCanvasEmpty()) {
                        e.preventDefault();
                        alert('Please provide your signature');
                        return false;
                    }
                    // Convert signature to base64 and set hidden input value
                    const signatureData = canvas.toDataURL();
                    document.getElementById('client_signature').value = signatureData;
                });
            }
        });

        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = getCoordinates(e);
        }

        function draw(e) {
            if (!isDrawing) return;

            e.preventDefault();

            const [currentX, currentY] = getCoordinates(e);

            context.beginPath();
            context.moveTo(lastX, lastY);
            context.lineTo(currentX, currentY);
            context.stroke();

            [lastX, lastY] = [currentX, currentY];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function getCoordinates(e) {
            let x, y;

            if (e.type.includes('touch')) {
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                x = touch.clientX - rect.left;
                y = touch.clientY - rect.top;
            } else {
                const rect = canvas.getBoundingClientRect();
                x = e.clientX - rect.left;
                y = e.clientY - rect.top;
            }

            return [x, y];
        }

        function handleTouchStart(e) {
            e.preventDefault();
            startDrawing(e);
        }

        function handleTouchMove(e) {
            e.preventDefault();
            draw(e);
        }

        function clearSignature() {
            context.clearRect(0, 0, canvas.width, canvas.height);
        }

        function isCanvasEmpty() {
            const pixelData = context.getImageData(0, 0, canvas.width, canvas.height).data;
            return !pixelData.some(channel => channel !== 0);
        }
    </script>
@endpush
