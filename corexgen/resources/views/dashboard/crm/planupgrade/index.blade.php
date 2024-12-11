@push('style')
    <style>
        .plan-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .plan-card-header {
            background: linear-gradient(135deg, var(--primary-color), #6366f1);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid white;
        }

        .plan-card-body {
            padding: 30px;
        }


        .plan-price-strike {
            font-size: 1rem;
            font-weight: 500;
            color: var(--primary-secondary);
            margin-bottom: 20px;
            text-align: center;
            text-decoration: line-through;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .plan-features {
            border-top: 1px solid rgba(0, 0, 0, 0.07);
            padding-top: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            /* color: var(--secondary-color); */
        }

        .feature-icon {
            color: var(--accent-color);
            margin-right: 12px;
            font-size: 1.2rem;
        }

        .btn-plan-action {
            width: 100%;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
    </style>
@endpush
@extends('layout.app')
@section('content')

    <div class="row justify-content-center">

        @if (isset($plans) && $plans->isNotEmpty())
            @foreach ($plans as $plan)
                <div class="col-md-4">
                    <div class="card plan-card">
                        <div class="plan-card-header">
                            <h3 class="mb-0">{{ $plan->name }} Plan</h3>
                            <p>{{ $plan->desc }} </p>
                        </div>


                        <div class="plan-card-body">
                            <div class="plan-price-strike">
                                {{ getSettingValue('Currency Symbol', '1') }} <span>{{ $plan->price }}
                                    ({{ getSettingValue('Currency Code', '1') }})
                                </span>
                            </div>
                            <div class="plan-price text-center" >
                                {{ getSettingValue('Currency Symbol', '1') }} <span id="plan_price">{{ $plan->offer_price }}</span> <span
                                    class="text-muted" style="font-size: 1rem;">/{{ $plan->billing_cycle }}
                                    ({{ getSettingValue('Currency Code', '1') }})</span>
                            </div>
                            @if ($current_plan_id === $plan->id)
                                <div class="text-center mt-3">
                                    <h4 class="mb-2">Current Plan</h4>
                                    <p class="mb-2 text-muted font-12">Renews on {{ $renew_at }}</p>
                                </div>
                            @else
                                <div class="text-center mt-3 ">
                                    <form method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                                        <input type="submit" value="Select" class="btn btn-primary mb-3 w-100" />
                                    </form>
                                </div>
                            @endif

                            @if ($plan->planFeatures)
                                <div class="plan-features">
                                    @foreach ($plan->planFeatures as $features)
                                        @if ($features->value === -1)
                                            <div class="feature-item">
                                                <span class="feature-icon">✓</span>
                                                Unlimited {{ ucwords($features->module_name) }} Create
                                            </div>
                                        @elseif($features->value > 0)
                                            <div class="feature-item">
                                                <span class="feature-icon">✓</span>
                                                {{ $features->value }} {{ ucwords($features->module_name) }} Create
                                            </div>
                                        @elseif($features->value === 0)
                                            <div class="feature-item text-muted">
                                                <span class="feature-icon">✗</span>
                                                {{ $features->value }} {{ ucwords($features->module_name) }} Create
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif


                        </div>
                    </div>
                </div>
            @endforeach
        @endif


    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            // Capture all plan selection form submissions
            $('.plan-card form').on('submit', function(e) {
                e.preventDefault(); // Stop the form from submitting immediately

                const $form = $(this);
                const planPrice = $("#plan_price") || 0;

                // Always show confirmation modal first
                const confirmModal = `
            <div class="modal fade" id="confirmPlanChangeModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Plan Change</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to change your plan? This action cannot be easily reversed.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="continueChangePlan">Continue</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Payment gateway modal
                const gatewayModal = `
            <div class="modal fade" id="selectPaymentGatewayModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Select Payment Gateway</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="paymentGateway" class="form-label">Choose Payment Method</label>
                                <select class="form-select" id="paymentGateway" required>
                                    <option value="">Select Payment Gateway</option>
                                    <option value="stripe">Stripe</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="proceedWithPayment">Proceed</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Append modals to body if not already exist
                if ($('#confirmPlanChangeModal').length === 0) {
                    $('body').append(confirmModal);
                }

                if ($('#selectPaymentGatewayModal').length === 0) {
                    $('body').append(gatewayModal);
                }

                // Initialize Bootstrap modals
                const confirmModalInstance = new bootstrap.Modal('#confirmPlanChangeModal');
                const gatewayModalInstance = new bootstrap.Modal('#selectPaymentGatewayModal');

                // Show confirmation modal
                confirmModalInstance.show();

                // Confirm plan change
                $('#continueChangePlan').off('click').on('click', function() {
                    confirmModalInstance.hide();

                    // If plan has a price, show payment gateway modal
                    if (planPrice > 0) {
                        gatewayModalInstance.show();
                    } else {
                        // If plan is free, directly submit the form
                        $form[0].submit();
                    }
                });

                // Proceed with payment
                $('#proceedWithPayment').off('click').on('click', function() {
                    const selectedGateway = $('#paymentGateway').val();

                    if (!selectedGateway) {
                        alert('Please select a payment gateway');
                        return;
                    }

                    // You would typically handle payment gateway logic here
                    // For now, we'll just submit the form with a hidden gateway input
                    $form.append(
                        `<input type="hidden" name="gateway" value="${selectedGateway}">`
                        );
                    gatewayModalInstance.hide();
                    $form[0].submit();
                });
            });
        });
    </script>
@endpush
