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
        /* Global Styles */
        body {
            font-family: 'Inter', sans-serif;
            background: var(--body-bg);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--body-color);
        }

        .container {
            padding: 0 1rem;
        }

        /* Onboarding Container */
        .onboarding-container {
            max-width: 600px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
            padding: 2rem;
        }

        /* Fade-in Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Steps */
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

        /* Progress Bar */
        .progress {
            height: 8px;
            background-color: var(--border-color);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .progress-bar {
            background: var(--primary-color);
            height: 100%;
            transition: width 0.5s ease;
        }

        /* Input Fields */
        .form-control {
            background-color: var(--input-bg);
            border: 2px solid var(--input-border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            color: var(--body-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(103, 61, 230, 0.2);
        }

        .is-invalid {
            border-color: var(--danger-color);
        }

        .is-invalid:focus {
            box-shadow: 0 4px 15px rgba(255, 60, 92, 0.2);
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Headings */
        h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* Alerts */
        .alert {
            border-radius: 10px;
            padding: 1rem;
            font-size: 1rem;
            background: rgba(103, 61, 230, 0.1);
            color: var(--primary-color);
            border: none;
        }

        /* Custom Select */
        select.form-control {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 4 5"%3E%3Cpath fill="%23636363" d="M2 0L0 2h4zM2 5L0 3h4z"/%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.75rem;
            background-color: var(--input-bg);
            color: var(--body-color);
        }

        select.form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(103, 61, 230, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .onboarding-container {
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn-primary {
                font-size: 0.9rem;
            }
        }

        .custom-alert {
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-out;
        }

        .custom-alert .bi-check-circle-fill {
            color: #28a745;
        }

        .custom-alert h4 {
            color: #6c63ff;
        }

        .custom-alert p,
        .custom-alert li {
            color: #343a40;
        }

        .custom-alert .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            width: 100%;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #onboarding {
            display: none;
        }

        #welcome {
            display: block;
        }


        .custom-modal {
            max-width: 500px;
            border-radius: 12px;
            overflow: hidden;
            animation: fadeIn 0.4s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .custom-modal .modal-content {
            background-color: #ffffff;
            border: none;
        }

        .custom-modal .modal-body {
            padding: 2rem;
        }

        .custom-modal .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .custom-modal p {
            font-size: 1rem;
            color: #616161;
        }

        .custom-modal ul {
            font-size: 1rem;
            color: #424242;
            padding-left: 0;
        }

        .custom-modal ul li {
            font-size: 1rem;
            color: #333333;
        }

        .custom-modal ul .badge {
            background-color: var(--primary-color);
            color: #ffffff;
            font-size: 0.9rem;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .custom-modal .btn-primary {
            background-color: var(--primary-color);
            border: none;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            padding: 0.75rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .custom-modal .btn-primary:hover {
            background-color: #371c89;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div id="welcome" class="modal fade show" tabindex="-1" aria-labelledby="registrationSuccessModal"
        aria-hidden="true" style="background-color: rgba(0, 0, 0, 0.6);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-body">

                    <div class="d-flex align-items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48"
                            class="me-3">
                            <path fill="#e0f7fa" d="M44,24c0,11-9,20-20,20S4,35,4,24S13,4,24,4S44,13,44,24z"></path>
                            <polyline fill="none" stroke="var(--primary-color)" stroke-miterlimit="10" stroke-width="4"
                                points="14,24 21,31 36,16"></polyline>
                        </svg>
                        <h4 class="modal-title">Registration Successful!</h4>
                    </div>

                    <p class="text-muted mb-3">Congratulations! Your company has been successfully registered.</p>
                    <p class="text-muted mb-4">Please follow these onboarding steps to continue your amazing journey
                        with us:</p>
                    <ul class="list-unstyled mb-5">
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">1</span> Complete your profile
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <span class="badge bg-primary me-2">2</span> Select Plan & Subscription
                        </li>
                        <li class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">3</span> Explore our platform
                        </li>
                    </ul>

                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="showOnboarding()">Start
                        Onboarding</button>
                </div>
            </div>
        </div>
    </div>




    <div class="container" id="onboarding">


        <div class="onboarding-container">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                    aria-valuemax="100"></div>
            </div>



            <form id="onboardingForm">


                <!-- Step 1: Company Address -->
                <div class="step active" id="addressStep">



                    <h2 class="mb-4">Company Address</h2>
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
                    <h2 class="mb-4">Currency Details</h2>
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
                           
                            @foreach ($payment_gateways as $pg)
                            <option value="{{ strtolower($pg->name) }}">{{ $pg->name }}</option>
                            @endforeach

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
        function showOnboarding() {
            $('#onboarding').show();
            $('#welcome').hide();
            $('#welcome').modal('hide');
        }
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
