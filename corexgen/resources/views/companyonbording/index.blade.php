<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Onboarding</title>

    <!-- Bootstrap CSS -->

    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/colors.css') }}" />

    <!-- Custom Styles -->
    <style>
        .onboarding-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .step {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .step.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .progress {
            height: 5px;
            background-color: #e0e0e0;
            margin-bottom: 2rem;
        }

        .progress-bar {
            background-color: var(--primary-color);
            transition: width 0.5s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(103, 61, 230, 0.25);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="onboarding-container">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                    aria-valuemax="100"></div>
            </div>

            <form id="onboardingForm">


                <!-- Step 1: Company Address -->
                <div class="step active" id="addressStep">
                    <h2 class="text-center mb-4">Company Address</h2>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <input type="text" class="form-control" name="address_street_address"
                                placeholder="Address Line 1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <select class="form-control" id="country_id" name="address_country_id" required>
                                <option value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <select class="form-control" id="city_id" name="address_city_id" required>
                                <option value="">Select City</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="address_pincode" placeholder="Postal Code"
                                required>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100" id="addressNextBtn">Next</button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Currency -->
                <div class="step" id="currencyStep">
                    <h2 class="text-center mb-4">Currency Details</h2>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="currency_code"
                                placeholder="Currency Code (e.g., USD)" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="currency_symbol"
                                placeholder="Currency Symbol ($)" required>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100" id="currencyNextBtn">Next</button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Timezone -->
                <div class="step" id="timezoneStep">
                    <h2 class="text-center mb-4">Select Timezone</h2>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <select class="form-control" name="timezone" required>
                                <option value="">Select Timezone</option>
                                @foreach ($timezones as $timezone)
                                    <option value="{{ $timezone }}">{{ $timezone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100" id="timezoneNextBtn">Next</button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Payment (if required) -->
                <div class="step" id="planStep">
                    <h2 class="text-center mb-4">Select Plan</h2>
                    <!-- Payment gateway integration goes here -->
                    <div class="col-12 mb-3">
                        <select class="form-control" name="plan_id" required>
                            <option value="">Select Plans</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="button" class="btn btn-primary w-100" id="planNextBtn">Select Plan</button>
                    </div>
                </div>

                <div class="step" id="paymentStep">
                    <h2 class="text-center mb-4">Select Payment</h2>
                    <!-- Payment gateway integration goes here -->
                    <div class="col-12 mb-3">
                        <select class="form-control" name="gateway" required>
                            <option value="">Select Payment</option>

                            <option value="stripe">Stripe</option>
                            <option value="paypal">Paypal</option>

                        </select>
                    </div>

                    <div class="col-12">
                        <button type="button" class="btn btn-primary w-100" id="paymentNextBtn">Select Payment
                            Type</button>
                    </div>
                </div>
                <div class="step" id="completeStep">
                    <h2 class="text-center mb-4">Complete</h2>
                    <div class="row">
                        <div class="alert alert-info">
                            <h5>Congradulations Onbording Steps Completed, Click to redirect to dashboard.</h5>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary w-100" id="completeNextBtn">Next</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('js/boostrap/bootstrap.bundle.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Get all the form steps and buttons
            const $steps = $('.step');
            const $progressBar = $('.progress-bar');
            const $form = $('#onboardingForm');

            // Function to validate the current step
            function validateStep($currentStep) {
                // Check all required inputs in the current step
                const $inputs = $currentStep.find('input, select');
                let isValid = true;

                $inputs.each(function() {
                    if (this.validity && !this.validity.valid) {
                        $(this).addClass('is-invalid');
                        isValid = false;
                    }
                });

                return isValid;
            }

            // Function to move to the next step
            function goToNextStep(currentStepId) {
                // Hide current step
                $(`#${currentStepId}`).removeClass('active');

                // Determine next step based on current step
                let nextStepId;
                switch (currentStepId) {
                    case 'addressStep':
                        nextStepId = 'currencyStep';
                        break;
                    case 'currencyStep':
                        nextStepId = 'timezoneStep';
                        break;
                    case 'timezoneStep':
                        nextStepId = 'planStep';
                        break;
                    case 'planStep':
                        nextStepId = 'paymentStep';
                        break;
                    case 'paymentStep':
                        nextStepId = 'completeStep';
                        break;
                    case 'completeStep':
                        nextStepId = 'dashboard';
                        break;
                }

                // Show next step
                $(`#${nextStepId}`).addClass('active');

                // Update progress bar
                const stepIndex = ['addressStep', 'currencyStep', 'timezoneStep', 'planStep', 'paymentStep',
                        'completeStep'
                    ]
                    .indexOf(currentStepId);
                const progress = ((stepIndex + 1) / 6) * 100;
                $progressBar
                    .css('width', `${progress}%`)
                    .attr('aria-valuenow', progress);

                return nextStepId;
            }

            // Function to submit step data
            function submitStepData($currentStep) {
                // Collect form data for the current step
                const formData = $currentStep.find('input, select').serializeArray();
                const stepId = $currentStep.attr('id');

                // Map step to endpoint
                const endpoints = {
                    'addressStep': '/onboarding/address',
                    'currencyStep': '/onboarding/currency',
                    'timezoneStep': '/onboarding/timezone',
                    'planStep': '/onboarding/plan',
                    'paymentStep': '/onboarding/payment',
                    'completeStep': '/onboarding/complete',
                };

                // Add CSRF token
                formData.push({
                    name: '_token',
                    value: '{{ csrf_token() }}'
                });

                // Send AJAX request
                return $.ajax({
                    url: endpoints[stepId],
                    method: 'POST',
                    data: formData
                });
            }

            // Event listener for all 'Next' buttons
            $('.step .btn-primary').on('click', function() {
                const $currentStep = $(this).closest('.step');
                const currentStepId = $currentStep.attr('id');

                // Validate current step
                if (!validateStep($currentStep)) {
                    return;
                }

                // Submit step data
                submitStepData($currentStep)
                    .done(function(response) {
                        // Handle special cases for plan step
                        if (currentStepId === 'planStep') {
                            if (response.nextStep === 'complete') {
                                // Free plan - go to complete step or redirect to company panel
                                if (response.redirectUrl) {
                                    // Directly redirect to the company panel
                                    window.location.href = response.redirectUrl;
                                    return;
                                }

                                $('#planStep').removeClass('active');
                                $('#completeStep').addClass('active');
                                return;
                            }
                        }

                        // New modification for payment step
                        if (currentStepId === 'paymentStep' && response.paymentUrl) {
                            // Redirect to payment gateway
                            window.location.href = response.paymentUrl;
                            return;
                        }

                        // Go to next step
                        const nextStepId = goToNextStep(currentStepId);
                    })
                    .fail(function(xhr) {
                        // Handle errors
                        const errors = xhr.responseJSON?.errors || {};
                        Object.keys(errors).forEach(field => {
                            $(`[name="${field}"]`).addClass('is-invalid');
                        });
                        alert('Please correct the errors in the form.');
                    });
            });

            // Event listener for complete step button
            // $('#completeStepBtn').on('click', function() {
            //     // Redirect to dashboard when complete step button is clicked
            //     window.location.href = '/dashboard';
            // });

            // Remove 'invalid' class when user starts typing
            $form.on('input', 'input, select', function() {
                $(this).removeClass('is-invalid');
            });
        });

        $('#country_id').on('change', function() {
            const countryId = $(this).val();
            const cityDropdown = $('#city_id');

            // Debug logs
            console.log('Selected Country ID:', countryId);

            // If no country is selected, reset and exit
            if (!countryId || countryId === "0") {
                cityDropdown.empty().append('<option value="">Select City</option>');
                cityDropdown.trigger('change'); // Trigger Select2 update
                return;
            }

            // Clear and show loading
            cityDropdown.empty().append('<option value="">Loading cities...</option>');
            cityDropdown.trigger('change'); // Trigger Select2 update

            // AJAX request
            $.ajax({
                url: `/get-cities/${countryId}`,
                method: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // More reliable CSRF token
                },
                success: function(response) {
                    console.log('Received cities:', response);

                    // Clear dropdown
                    cityDropdown.empty().append('<option value="">Select City</option>');

                    // Populate dropdown
                    response.forEach(city => {
                        const option = new Option(city.name, city.id);
                        cityDropdown.append(option);
                    });

                    // Reinitialize Select2 and trigger change
                    cityDropdown.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);

                    cityDropdown.empty().append('<option value="">Error loading cities</option>');
                    cityDropdown.trigger('change');

                    alert('Failed to load cities. Please try again.');
                }
            });
        });
    </script>
