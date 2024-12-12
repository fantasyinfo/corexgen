@extends('layout.app')

@section('content')


    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="companyForm" action="{{ route(getPanelRoutes('companies.store')) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('companies.Create New Company') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('companies.Create Company') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('companies.General') }}
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address"
                                        type="button" role="tab">
                                        {{ __('companies.Address') }}
                                    </button>
                                </li>



                            </ul>


                            <div class="tab-content mt-4" id="companyTabsContent">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="companyName" class="custom-class" required>
                                                {{ __('companies.Company Name') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="cname" id="companyName"
                                                placeholder="{{ __('Enter Company Name') }}" value="{{ old('cname') }}"
                                                required class="custom-class" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayUserName" class="custom-class"
                                                required>
                                                {{ __('companies.Full Name') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="name"
                                                id="compnayUserName" placeholder="{{ __('John Doe') }}"
                                                value="{{ old('name') }}" required class="custom-class" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayEmail" class="custom-class" required>
                                                {{ __('companies.Email') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="email" name="email" id="compnayEmail"
                                                placeholder="{{ __('john@email.com') }}" value="{{ old('email') }}"
                                                required class="custom-class" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayPhone" class="custom-class" required>
                                                {{ __('companies.Phone') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="tel" name="phone" id="compnayPhone"
                                                placeholder="{{ __('9876543210') }}" value="{{ old('phone') }}" required
                                                class="custom-class" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayPassword" class="custom-class"
                                                required>
                                                {{ __('companies.Password') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="password" name="password"
                                                id="compnayPassword" placeholder="{{ __('*********') }}"
                                                value="{{ old('password') }}" required class="custom-class" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayPlan" class="custom-class" required>
                                                {{ __('companies.Select Plan') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('plan_id') is-invalid @enderror"
                                                    name="plan_id" id="plan_id">
                                                    @if ($plans)
                                                        @foreach ($plans as $plan)
                                                            <option value="{{ $plan->id }}"
                                                                {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                                                {{ $plan->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No plans available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="country_idError">
                                                    @error('plan_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="alert  alert-warning p-1 rounded border">
                                        <div class="alert-warning">
                                            <i class="fas fa-info-circle me-2 "></i> Please add Address details on <span
                                                class="text-success">Address Tab</span> . Payment Method will be <span
                                                class="text-danger">offline</span>, if compnay is created via this panel.
                                        </div>
                                    </div>
                                </div>


                                <!-- Addresses Tab -->
                                <div class="tab-pane fade" id="address" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressStreet"
                                                class="custom-class">
                                                {{ __('address.Address') }}
                                            </x-form-components.input-label>
                                        </div>

                                        <div class="col-lg-8">
                                            <x-form-components.textarea-group name="address.street_address"
                                                id="compnayAddressStreet" placeholder="Enter Registered Street Address"
                                                class="custom-class" value="{{ old('address.street_address')}}" />

                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressCountry"
                                                class="custom-class">
                                                {{ __('address.Country') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('address.country_id') is-invalid @enderror"
                                                    name="address.country_id" id="country_id">
                                                    <option value="0" selected> ----- Select Country ----------
                                                    </option>
                                                    @if ($country)
                                                        @foreach ($country as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ old('address.country_id') == $country->id ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No country available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="country_idError">
                                                    @error('address.country_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressCity" class="custom-class">
                                                {{ __('address.City') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <select
                                                class="form-control searchSelectBox @error('address.city_id') is-invalid @enderror"
                                                name="address.city_id" id="city_id">
                                                <option value="0" selected> ----- Select City ----------</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="compnayAddressPincode"
                                                class="custom-class">
                                                {{ __('address.Pincode') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="address.pincode"
                                                id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                                value="{{ old('address.pincode') }}" class="custom-class" />

                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add custom tab validation or handling
            const tabs = document.querySelectorAll('#companyTabs .nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // You can add custom logic here if needed
                    console.log(`Switched to tab: ${this.textContent}`);
                });
            });





        });

        console.log(document.getElementById('country_id'))
        // country and cities
        // Use Select2's event instead of native change
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
@endpush
