@extends('layout.guest')

@push('style')
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .form-label {
            font-weight: bold;
        }



        .invalid-feedback {
            display: none;
        }

        .is-invalid~.invalid-feedback {
            display: block;
        }

     
    </style>
@endpush

@section('content')
    <div class="container-fluid py-5">
        <div class="row">
            <div class="col-10 col-md-8 col-lg-6 mx-auto form-container">
                <h2 class="mb-4 text-center">Lead Form: {{ $formData->title ?? '' }}</h2>

                <form method="POST" action="{{ route('leadFormStore') }}" id="leadForm" novalidate>
                    @csrf

                    {{-- Hidden Fields --}}
                    <input type="hidden" name="web_to_leads_form_id" value="{{ $formData->id ?? '' }}">
                    <input type="hidden" name="web_to_leads_form_uuid" value="{{ $formData->uuid ?? '' }}">
                    <input type="hidden" name="title" value="{{ $formData->title ?? '' }}">
                    <input type="hidden" name="group_id" value="{{ $formData->group_id ?? '' }}">
                    <input type="hidden" name="source_id" value="{{ $formData->source_id ?? '' }}">
                    <input type="hidden" name="status_id" value="{{ $formData->status_id ?? '' }}">
                    <input type="hidden" name="company_id" value="{{ $formData->company_id ?? '' }}">

                    {{-- Fields --}}
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" name="type" id="type" required>
                            <option value="Individual">Individual</option>
                            <option value="Company">Company</option>
                        </select>
                        <div class="invalid-feedback">Please enter a valid company name.</div>
                    </div>
                    <div class="mb-3" >
                        <label for="company_name" class="form-label">Company Name </label>
                        <input type="text" class="form-control" id="company_name" name="company_name"
                            placeholder="Enter Company Name (Optional)" >
                        <div class="invalid-feedback">Please enter a valid company name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            placeholder="Enter First Name" required>
                        <div class="invalid-feedback">Please enter a valid first name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            placeholder="Enter Last Name" required>
                        <div class="invalid-feedback">Please enter a valid last name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email"
                            required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter Phone">
                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                    </div>

                    <div class="mb-3">
                        <label for="preferred_contact_method" class="form-label">Preferred Contact Method <span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="preferred_contact_method" id="preferred_contact_method" required>
                            <option value="" disabled selected>-- Select Method --</option>
                            @foreach (['Email', 'Phone', 'In-Person'] as $pcm)
                                <option value="{{ $pcm }}">{{ $pcm }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a contact method.</div>
                    </div>

                    <div class="mb-3">
                        <label for="compnayAddressStreet" class="form-label">Address</label>
                        <textarea class="form-control" name="address[street_address]" id="compnayAddressStreet" placeholder="Enter Address"></textarea>
                        <div class="invalid-feedback">Please enter a valid address.</div>
                    </div>

                    <div class="mb-3">
                        <label for="country_id" class="form-label">Country</label>
                        <select class="form-select" name="address[country_id]" id="country_id">
                            <option value="" disabled selected>-- Select Country --</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a country.</div>
                    </div>

                    <div class="mb-3">
                        <label for="compnayAddressCity" class="form-label">City</label>
                        <input type="text" class="form-control" id="compnayAddressCity" name="address[city_name]"
                            placeholder="Enter City">
                        <div class="invalid-feedback">Please enter a valid city.</div>
                    </div>

                    <div class="mb-3">
                        <label for="compnayAddressPincode" class="form-label">Pincode</label>
                        <input type="text" class="form-control" id="compnayAddressPincode" name="address[pincode]"
                            placeholder="Enter Pincode">
                        <div class="invalid-feedback">Please enter a valid pincode.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('leadForm').addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Get all required fields
            const requiredFields = this.querySelectorAll('input[required], select[required], textarea[required]');

            // Reset all validation states
            this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Validate each required field
            requiredFields.forEach(field => {
                switch (field.type) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(field.value.trim())) {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                        break;

                    case 'tel':
                    case 'text':
                        if (field.value.trim() === '') {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                        break;

                    case 'select-one':
                        if (!field.value || field.value === '') {
                            field.classList.add('is-invalid');
                            isValid = false;
                        }
                        break;
                }
            });

            // Phone validation (even if not required)
            const phoneInput = this.querySelector('#phone');
            if (phoneInput.value.trim() !== '') {
                const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
                if (!phoneRegex.test(phoneInput.value.trim())) {
                    phoneInput.classList.add('is-invalid');
                    isValid = false;
                }
            }

            // If form is valid, submit it
            if (isValid) {
                this.submit();
            }
        });

        // Real-time validation on input
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                }
            });
        });

     
    </script>
@endpush
