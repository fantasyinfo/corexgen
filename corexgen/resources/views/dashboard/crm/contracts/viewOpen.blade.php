@extends('layout.guest')

@push('style')
    <style>
        :root {
            --proposal-primary: #f23636;
            --proposal-bg: var(--card-bg);
            --proposal-text: var(--body-color);
            --proposal-border: var(--border-color);
        }

        .backbg {
            background-color: var(--proposal-primary);
        }

        .backbg-primary {
            background-color: #c30606;
        }

        .proposal-container {
            max-width: 1140px;
            margin: 0 auto;
            background: var(--proposal-bg);
        }

        .proposal-header {
            position: relative;
            padding: 2.5rem;
            background: linear-gradient(45deg, #f23636, #ec7063);
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
                <div class="watermark">CONTRACT</div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <span class="status-badge  backbg-primary text-dark mb-3">
                                {{ $contract?->_prefix }}-{{ $contract?->_id }}
                            </span>
                            <h1 class="display-4 mb-2">{{ $contract?->title }}</h1>
                            <p class="lead mb-0">Prepared for {{ $contract?->typable->title }}.
                                {{ $contract?->typable?->first_name }} {{ $contract?->typable?->last_name }}</p>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Prepared By</small>
                                <h4>{{ $contract?->company?->name }}</h4>
                                <p class="mb-0">{{ $contract?->company?->addresses?->street_address }}</p>
                                <p class="mb-0">{{ $contract?->company?->addresses?->city?->name }},
                                    {{ $contract?->company?->addresses?->country?->name }}</p>
                                <p class="mb-0">{{ $contract?->company?->phone }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-light opacity-75">Valid Until</small>
                                <h4>{{ $contract?->valid_date ? \Carbon\Carbon::parse($contract?->valid_date)->format('F d, Y') : 'Not Specified' }}
                                </h4>
                                <p class="mb-0">Created:
                                    {{ \Carbon\Carbon::parse($contract?->creating_date)->format('F d, Y') }}</p>


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
                    @if ($contract?->statusCompany != true)
                        <button class="btn btn-dark me-2" data-bs-toggle="modal" data-bs-target="#signedCompanyModal">
                            <i class="fas fa-check me-2"></i>Accept Contract For Company
                        </button>
                    @endif

                    <button class="btn btn-outline-secondary me-2" onclick="printProposal()">
                        <i class="fas fa-down me-2"></i>Download PDF
                    </button>
                    @if ($contract?->status !== 'ACCEPTED')
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#signedModal">
                            <i class="fas fa-check me-2"></i>Accept Contract For Client
                        </button>
                    @endif

                </div>


                @if ($contract?->status !== 'ACCEPTED')
                    <div class="modal fade" id="signedModal" tabindex="-1" aria-labelledby="signedModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered ">
                            <form action="{{ route('contract.accept') }}" method="POST" id="acceptProposalForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $contract->id }}" />
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
                                                    onclick="clearSignature('signaturePad')">Clear Signature</button>
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

                @if ($contract?->statusCompany != true)
                    <div class="modal fade" id="signedCompanyModal" tabindex="-1"
                        aria-labelledby="signedCompanyModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered ">
                            <form action="{{ route('contract.acceptCompany') }}" method="POST"
                                id="acceptCompanyProposalForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $contract->id }}" />
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
                                                <input type="text" id="first_name" name="first_name"
                                                    class="form-control" placeholder="Enter your first name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" id="last_name" name="last_name"
                                                    class="form-control" placeholder="Enter your last name" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="company_email" class="form-label">Your Email</label>
                                                <input type="email" id="company_email" name="email"
                                                    class="form-control" placeholder="Enter your email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="company_signature" class="form-label">Digital
                                                    Signature</label>
                                                <div>
                                                    <canvas id="signaturePadCompany" class="border" width="400px"
                                                        height="200"></canvas>
                                                </div>

                                                <input type="hidden" id="company_signature" name="signature" required>
                                                <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                    onclick="clearSignature('signaturePadCompany')">Clear
                                                    Signature</button>
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

        // Initialize signature pad when document is loaded

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables with null checks
            const clientElements = {
                canvas: document.getElementById('signaturePad'),
                context: document.getElementById('signaturePad')?.getContext('2d'),
                form: document.getElementById('acceptProposalForm'),
                signatureField: document.getElementById('client_signature'),
                isDrawing: false,
                lastX: 0,
                lastY: 0
            };

            const companyElements = {
                canvas: document.getElementById('signaturePadCompany'),
                context: document.getElementById('signaturePadCompany')?.getContext('2d'),
                form: document.getElementById('acceptCompanyProposalForm'),
                signatureField: document.getElementById('company_signature'),
                isDrawing: false,
                lastX: 0,
                lastY: 0
            };

            // Initialize signature pad for an element set
            function initializeSignaturePad(elements) {
                if (!elements.canvas || !elements.context) return;

                elements.context.strokeStyle = '#000000';
                elements.context.lineWidth = 2;
                elements.context.lineCap = 'round';

                // Mouse events
                elements.canvas.addEventListener('mousedown', (e) => startDrawing(e, elements));
                elements.canvas.addEventListener('mousemove', (e) => draw(e, elements));
                elements.canvas.addEventListener('mouseup', () => stopDrawing(elements));
                elements.canvas.addEventListener('mouseout', () => stopDrawing(elements));

                // Touch events
                elements.canvas.addEventListener('touchstart', (e) => handleTouchStart(e, elements));
                elements.canvas.addEventListener('touchmove', (e) => handleTouchMove(e, elements));
                elements.canvas.addEventListener('touchend', () => stopDrawing(elements));
            }

            // Initialize both pads if they exist
            initializeSignaturePad(clientElements);
            initializeSignaturePad(companyElements);

            // Drawing functions
            function startDrawing(e, elements) {
                if (!elements.canvas || !elements.context) return;

                const rect = elements.canvas.getBoundingClientRect();
                elements.isDrawing = true;
                [elements.lastX, elements.lastY] = getCoordinates(e, rect);
            }

            function draw(e, elements) {
                if (!elements.isDrawing || !elements.context || !elements.canvas) return;

                e.preventDefault();
                const rect = elements.canvas.getBoundingClientRect();
                const [currentX, currentY] = getCoordinates(e, rect);

                elements.context.beginPath();
                elements.context.moveTo(elements.lastX, elements.lastY);
                elements.context.lineTo(currentX, currentY);
                elements.context.stroke();

                [elements.lastX, elements.lastY] = [currentX, currentY];
            }

            function stopDrawing(elements) {
                if (!elements) return;
                elements.isDrawing = false;
            }

            function getCoordinates(e, rect) {
                let x, y;
                if (e.type.includes('touch')) {
                    const touch = e.touches[0];
                    x = touch.clientX - rect.left;
                    y = touch.clientY - rect.top;
                } else {
                    x = e.clientX - rect.left;
                    y = e.clientY - rect.top;
                }
                return [x, y];
            }

            function handleTouchStart(e, elements) {
                e.preventDefault();
                startDrawing(e, elements);
            }

            function handleTouchMove(e, elements) {
                e.preventDefault();
                draw(e, elements);
            }

            // Form submission handlers
            if (clientElements.form && clientElements.canvas && clientElements.context) {
                clientElements.form.addEventListener('submit', function(e) {
                    if (isCanvasEmpty(clientElements.context, clientElements.canvas)) {
                        e.preventDefault();
                        alert('Please provide your signature');
                        return false;
                    }
                    if (clientElements.signatureField) {
                        clientElements.signatureField.value = clientElements.canvas.toDataURL();
                    }
                });
            }

            if (companyElements.form && companyElements.canvas && companyElements.context) {
                companyElements.form.addEventListener('submit', function(e) {
                    if (isCanvasEmpty(companyElements.context, companyElements.canvas)) {
                        e.preventDefault();
                        alert('Please provide your signature');
                        return false;
                    }
                    if (companyElements.signatureField) {
                        companyElements.signatureField.value = companyElements.canvas.toDataURL();
                    }
                });
            }

            // Helper function
            function isCanvasEmpty(context, canvas) {
                if (!context || !canvas) return true;
                const pixelData = context.getImageData(0, 0, canvas.width, canvas.height).data;
                return !pixelData.some(channel => channel !== 0);
            }

            // Clear signature function (make it available globally)
            window.clearSignature = function(canvasId) {
                const targetCanvas = document.getElementById(canvasId);
                const targetContext = targetCanvas?.getContext('2d');
                if (targetContext && targetCanvas) {
                    targetContext.clearRect(0, 0, targetCanvas.width, targetCanvas.height);
                }
            };
        });
    </script>
@endpush
