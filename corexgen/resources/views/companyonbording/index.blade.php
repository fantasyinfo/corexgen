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
                        <button type="button" class="btn btn-primary w-100" id="paymentNextBtn">Select Payment Type</button>
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
            // Cache DOM elements
            const $form = $('#onboardingForm');
            const $progressBar = $('.progress-bar');
            const $steps = $('.step');

            // Step navigation buttons
            const $addressNextBtn = $('#addressNextBtn');
            const $currencyNextBtn = $('#currencyNextBtn');
            const $timezoneNextBtn = $('#timezoneNextBtn');
            const $planNextBtn = $('#planNextBtn');
            const $paymentNextBtn = $('#paymentNextBtn');
            const $paymentBtn = $('#paymentBtn');

            // Define steps
            const steps = [{
                    id: 'addressStep',
                    endpoint: '/onboarding/address',
                    nextStep: 'currencyStep'
                },
                {
                    id: 'currencyStep',
                    endpoint: '/onboarding/currency',
                    nextStep: 'timezoneStep'
                },
                {
                    id: 'timezoneStep',
                    endpoint: '/onboarding/timezone',
                    nextStep: 'planStep'
                },
                {
                    id: 'planStep',
                    endpoint: '/onboarding/plan',
                    nextStep: 'paymentStep'
                },
                {
                    id: 'paymentStep',
                    endpoint: '/onboarding/payment',
                    nextStep: 'dashboard'
                }
            ];

         
            // Step validation and progression
            function validateAndSubmitStep(stepIndex) {
                const currentStep = steps[stepIndex];
                const $currentStepElement = $(`#${currentStep.id}`);

                // Validate inputs within the current step
                const stepInputs = $currentStepElement.find('input, select');
                let isValid = true;

                stepInputs.each(function() {
                    if (this.validity && !this.validity.valid) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    }
                });

                if (!isValid) return false;

                // Collect form data for the current step
                const formData = $currentStepElement.find('input, select')
                    .serializeArray()
                    .reduce((obj, item) => {
                        obj[item.name] = item.value;
                        return obj;
                    }, {});

                // AJAX submission
                formData['_token'] = '{{ csrf_token() }}';
                $.ajax({
                    url: currentStep.endpoint,
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Move to next step
                            $currentStepElement.removeClass('active');
                            $(`#${currentStep.nextStep}`).addClass('active');

                            // Update progress bar
                            const progress = ((stepIndex + 1) / steps.length) * 100;
                            $progressBar
                                .css('width', `${progress}%`)
                                .attr('aria-valuenow', progress);

                            // Special handling for timezone step (check payment requirement)
                            if (currentStep.id === 'planStep' && response.nextStep ===
                                'dashboard') {
                                window.location.href = '/dashboard';
                            }
                        } else {
                            alert(response.message || 'An error occurred');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        Object.keys(errors).forEach(field => {
                            $(`[name="${field}"]`).addClass('is-invalid');
                        });
                        alert('Please correct the errors in the form.');
                    }
                });
            }

            // Event Listeners for Next buttons
            $addressNextBtn.on('click', () => validateAndSubmitStep(0));
            $currencyNextBtn.on('click', () => validateAndSubmitStep(1));
            $timezoneNextBtn.on('click', () => validateAndSubmitStep(2));
            $planNextBtn.on('click', () => validateAndSubmitStep(3));
            $paymentNextBtn.on('click', () => validateAndSubmitStep(4));
            //$paymentBtn.on('click', () => validateAndSubmitStep(3));

            // Remove invalid class on input
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
