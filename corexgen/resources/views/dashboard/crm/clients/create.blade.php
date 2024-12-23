@extends('layout.app')

@section('content')
 
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="clientForm" action="{{ route(getPanelRoutes('clients.store')) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('clients.Create New Client') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('clients.Create Client') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="clientsTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('clients.General Information') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                                        type="button" role="tab">
                                        {{ __('clients.Contact Details') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address"
                                        type="button" role="tab">
                                        {{ __('clients.Address') }}
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="additional-tab" data-bs-toggle="tab"
                                        data-bs-target="#additional" type="button" role="tab">
                                        {{ __('clients.Additional Information') }}
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-4" id="clientsTabsContent">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="clientType" required>
                                                {{ __('clients.Client Type') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="type" id="clientType" required>
                                                <option value="Individual">Individual</option>
                                                <option value="Company">Company</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4" id="company_name_div">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="companyName" required>
                                                {{ __('clients.Company Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="company_name"
                                                id="companyName" placeholder="{{ __('Abc Pvt Ltd') }}"
                                                value="{{ old('company_name') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="clientTitle">
                                                {{ __('clients.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="title" id="clientTitle"
                                                placeholder="{{ __('Mr./Mrs./Ms.') }}" value="{{ old('title') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="firstName" required>
                                                {{ __('clients.First Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="first_name" id="firstName"
                                                placeholder="{{ __('First Name') }}" value="{{ old('first_name') }}"
                                                required />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="middleName">
                                                {{ __('clients.Middle Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="middle_name" id="middleName"
                                                placeholder="{{ __('Middle Name') }}" value="{{ old('middle_name') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="lastName" required>
                                                {{ __('clients.Last Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" name="last_name" id="lastName"
                                                placeholder="{{ __('Last Name') }}" value="{{ old('last_name') }}"
                                                required />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="birthdate">
                                                {{ __('clients.Birth Date') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="date" name="birthdate" id="birthdate"
                                                value="{{ old('birthdate') }}" />
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="category">
                                                {{ __('clients.Category') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select" name="cgt_id" id="cgt_id">
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <hr>
                                    <p class="alert alert-secondary"><i class="fas fa-info-circle me-2 "></i>
                                        Please add / update <span class="text-success">Contact Details</span> on contact
                                        details tabs.</p>
                                </div>

                                <!-- Contact Details Tab -->
                                <div class="tab-pane fade" id="contact" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="emails">
                                                {{ __('clients.Email Addresses') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="emailContainer">
                                                <div class="input-group mb-2">
                                                    <input type="email" name="email[]" class="form-control"
                                                        placeholder="Email Address">
                                                    <button type="button" class="btn btn-outline-secondary add-email">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <p class="px-2 font-12 my-2 text-secondary">First email will be primary
                                                    email for this client.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="phones">
                                                {{ __('clients.Phone Numbers') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="phoneContainer">
                                                <div class="input-group mb-2">
                                                    <input type="tel" name="phone[]" class="form-control"
                                                        placeholder="Phone Number">
                                                    <button type="button" class="btn btn-outline-secondary add-phone">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <p class="px-2 font-12 my-2 text-secondary">First phone number will be
                                                    primary phone number for this client.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="socialMedia">
                                                {{ __('clients.Social Media Facebook') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="socialMediaContainer">
                                                <div class="input-group mb-2">
                                                    <input type="url" name="social_media['fb']" class="form-control"
                                                        placeholder="Facebook Profile URL">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="socialMedia">
                                                {{ __('clients.Social Media LinkedIn') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="socialMediaContainer">
                                                <div class="input-group mb-2">
                                                    <input type="url" name="social_media['ln']" class="form-control"
                                                        placeholder="LinkedIn Profile URL">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="socialMedia">
                                                {{ __('clients.Social Media Instagram') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="socialMediaContainer">
                                                <div class="input-group mb-2">
                                                    <input type="url" name="social_media['in']" class="form-control"
                                                        placeholder="Instragram Profile URL">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="socialMedia">
                                                {{ __('clients.Social Media X') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div id="socialMediaContainer">
                                                <div class="input-group mb-2">
                                                    <input type="url" name="social_media['x']" class="form-control"
                                                        placeholder="X (Twitter) Profile URL">

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <p class="alert alert-secondary"><i class="fas fa-info-circle me-2 "></i>
                                        Please add / update <span class="text-success">Address Details</span> on address
                                        tabs.</p>
                                </div>

                                <!-- Address Tab -->
                                <div class="tab-pane fade" id="address" role="tabpanel">
                                    <div id="addressContainer">
                                        <div class="address-block mb-4 p-3 border rounded">
                                            <div class="row mb-3">
                                                <div class="col-lg-4">
                                                    <x-form-components.input-label for="addressType">
                                                        {{ __('clients.Address Type') }}
                                                    </x-form-components.input-label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <select name="addresses[0][type]" class="form-select">
                                                        <option value="home">Home</option>
                                                        <option value="billing">Billing</option>
                                                        <option value="shipping">Shipping</option>
                                                        <option value="custom">Custom</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4">
                                                    <x-form-components.input-label for="streetAddress">
                                                        {{ __('address.Street Address') }}
                                                    </x-form-components.input-label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <x-form-components.textarea-group name="addresses[0][street_address]"
                                                        placeholder="Enter Street Address" rows="3" />
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4">
                                                    <x-form-components.input-label for="country">
                                                        {{ __('address.Country') }}
                                                    </x-form-components.input-label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <select name="addresses[0][country_id]"
                                                        class="form-select searchSelectBox">
                                                        <option value="">Select Country</option>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}">{{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4">
                                                    <x-form-components.input-label for="city">
                                                        {{ __('address.City') }}
                                                    </x-form-components.input-label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <x-form-components.input-group type="text"
                                                        name="addresses[0][city]" placeholder="Enter City" />
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4">
                                                    <x-form-components.input-label for="pincode">
                                                        {{ __('address.Pincode') }}
                                                    </x-form-components.input-label>
                                                </div>
                                                <div class="col-lg-8">
                                                    <x-form-components.input-group type="text"
                                                        name="addresses[0][pincode]" placeholder="Enter Pincode" />
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary add-address">
                                            <i class="fas fa-plus"></i> Add Another Address
                                        </button>
                                    </div>
                                    <hr>
                                    <p class="alert alert-secondary"><i class="fas fa-info-circle me-2 "></i>
                                        Please add / update <span class="text-success">Additional Details</span> on
                                        additional details tabs.</p>
                                </div>

                                <!-- Additional Information Tab -->
                                <div class="tab-pane fade" id="additional" role="tabpanel">


                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="tags">
                                                {{ __('clients.Tags') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select name="tags[]" id="tags" class="form-select" multiple>
                                                <!-- Add your tag options here -->
                                            </select>

                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="details">
                                                {{ __('clients.Additional Details') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <textarea name="details" id="details" class="form-control wysiwyg-editor" rows="5">{{ old('details') }}</textarea>
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
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#clientType').on('change', function() {
                var selectedType = $(this).val();
                if (selectedType === 'Company') {
                    $('#company_name_div').show();
                } else {
                    $('#company_name_div').hide();
                }
            });

            // Trigger change event on page load
            $('#clientType').trigger('change');
        });





        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2 for searchable dropdowns
            $('.searchSelectBox').select2({
                width: '100%',
                placeholder: 'Select an option'
            });

            // Initialize tags with old values
            $('#tags').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: 'Enter tags'
            });

            // Set old tags if they exist
            if (@json(old('tags', []))) {
                const oldTags = @json(old('tags', []));
                $('#tags').val(oldTags).trigger('change');
            }

            // Initialize social media with old values
            if (@json(old('social_media', []))) {
                const oldSocialMedia = @json(old('social_media', []));
                Object.keys(oldSocialMedia).forEach(platform => {
                    const input = document.querySelector(`input[name="social_media[${platform}]"]`);
                    if (input) {
                        input.value = oldSocialMedia[platform];
                    }
                });
            }

            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            // Initialize WYSIWYG editor
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.wysiwyg-editor',
                    height: 300,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! old('details', '') !!}`);
                        });
                    },
                    menubar: false,
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        'autoresize',
                        'autosave',
                        'charmap',
                        'code',
                        'codesample',
                        'directionality',
                        'emoticons',
                        'fullscreen',
                        'help',
                        'lists',
                        'link',
                        'image',


                        'preview',
                        'anchor',
                        'searchreplace',
                        'visualblocks',


                        'insertdatetime',
                        'media',
                        'table',



                        'wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \
                      alignleft aligncenter alignright alignjustify | \
                      bullist numlist outdent indent | removeformat | help | \
                      link image media preview codesample table'
                });
            }

            // Handle dynamic email fields
            document.querySelector('.add-email').addEventListener('click', function() {
                const container = document.getElementById('emailContainer');
                const newGroup = document.createElement('div');
                newGroup.className = 'input-group mb-2';
                newGroup.innerHTML = `
                    <input type="email" name="email[]" class="form-control" placeholder="Email Address">
                    <button type="button" class="btn btn-outline-danger remove-field">
                        <i class="fas fa-minus"></i>
                    </button>
                `;
                container.appendChild(newGroup);
                attachValidationListeners(newGroup.querySelector('input'));
            });

            // Handle dynamic phone fields
            document.querySelector('.add-phone').addEventListener('click', function() {
                const container = document.getElementById('phoneContainer');
                const newGroup = document.createElement('div');
                newGroup.className = 'input-group mb-2';
                newGroup.innerHTML = `
                    <input type="tel" name="phone[]" class="form-control" placeholder="Phone Number">
                    <button type="button" class="btn btn-outline-danger remove-field">
                        <i class="fas fa-minus"></i>
                    </button>
                `;
                container.appendChild(newGroup);
                attachValidationListeners(newGroup.querySelector('input'));
            });

            // Handle old email values
            if (@json(old('email', []))) {
                const oldEmails = @json(old('email', []));
                oldEmails.forEach((email, index) => {
                    if (index === 0) {
                        document.querySelector('input[name="email[]"]').value = email;
                    } else {
                        const container = document.getElementById('emailContainer');
                        const newGroup = document.createElement('div');
                        newGroup.className = 'input-group mb-2';
                        newGroup.innerHTML = `
                            <input type="email" name="email[]" class="form-control" value="${email}" placeholder="Email Address">
                            <button type="button" class="btn btn-outline-danger remove-field">
                                <i class="fas fa-minus"></i>
                            </button>
                        `;
                        container.appendChild(newGroup);
                        attachValidationListeners(newGroup.querySelector('input'));
                    }
                });
            }

            // Handle old phone values
            if (@json(old('phone', []))) {
                const oldPhones = @json(old('phone', []));
                oldPhones.forEach((phone, index) => {
                    if (index === 0) {
                        document.querySelector('input[name="phone[]"]').value = phone;
                    } else {
                        const container = document.getElementById('phoneContainer');
                        const newGroup = document.createElement('div');
                        newGroup.className = 'input-group mb-2';
                        newGroup.innerHTML = `
                            <input type="tel" name="phone[]" class="form-control" value="${phone}" placeholder="Phone Number">
                            <button type="button" class="btn btn-outline-danger remove-field">
                                <i class="fas fa-minus"></i>
                            </button>
                        `;
                        container.appendChild(newGroup);
                        attachValidationListeners(newGroup.querySelector('input'));
                    }
                });
            }

            // Handle dynamic address blocks
            let addressCount = 1;
            document.querySelector('.add-address').addEventListener('click', function() {
                const container = document.getElementById('addressContainer');
                const newAddress = createAddressBlock(addressCount);
                container.insertBefore(newAddress, this);

                // Initialize Select2 for new address fields
                $(newAddress).find('.searchSelectBox').select2({
                    width: '100%',
                    placeholder: 'Select an option'
                });

                // Attach validation listeners to new fields
                newAddress.querySelectorAll('input, select, textarea').forEach(field => {
                    attachValidationListeners(field);
                });

                addressCount++;
            });

            // Function to create address block
            function createAddressBlock(index) {
                const newAddress = document.createElement('div');
                newAddress.className = 'address-block mb-4 p-3 border rounded';
                newAddress.innerHTML = `
                    <button type="button" class="btn btn-outline-danger btn-sm remove-block">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <x-form-components.input-label>
                                {{ __('clients.Address Type') }}
                            </x-form-components.input-label>
                        </div>
                        <div class="col-lg-8">
                            <select name="addresses[${index}][type]" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="home">Home</option>
                                <option value="billing">Billing</option>
                                <option value="shipping">Shipping</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <x-form-components.input-label>
                                {{ __('address.Street Address') }}
                            </x-form-components.input-label>
                        </div>
                        <div class="col-lg-8">
                            <textarea name="addresses[${index}][street_address]" class="form-control" rows="3" placeholder="Enter Street Address" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <x-form-components.input-label>
                                {{ __('address.Country') }}
                            </x-form-components.input-label>
                        </div>
                        <div class="col-lg-8">
                            <select name="addresses[${index}][country_id]" class="form-select searchSelectBox" required>
                                <option value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <x-form-components.input-label>
                                {{ __('address.City') }}
                            </x-form-components.input-label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" name="addresses[${index}][city]" class="form-control" placeholder="Enter City" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-lg-4">
                            <x-form-components.input-label>
                                {{ __('address.Pincode') }}
                            </x-form-components.input-label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" name="addresses[${index}][pincode]" class="form-control" placeholder="Enter Pincode" required>
                        </div>
                    </div>
                `;
                return newAddress;
            }

            // Handle old addresses
            if (@json(old('addresses', []))) {
                const oldAddresses = @json(old('addresses', []));
                Object.entries(oldAddresses).forEach(([index, address]) => {
                    if (index === '0') {
                        // Fill the first address block that already exists
                        fillAddressBlock(document.querySelector('.address-block'), address, 0);
                    } else {
                        // Create new address blocks for additional addresses
                        const container = document.getElementById('addressContainer');
                        const newAddress = createAddressBlock(index);
                        container.insertBefore(newAddress, container.querySelector('.add-address'));
                        fillAddressBlock(newAddress, address, index);

                        // Initialize Select2 for the new address block
                        $(newAddress).find('.searchSelectBox').select2({
                            width: '100%',
                            placeholder: 'Select an option'
                        });
                    }
                });
            }

            // Function to fill address block with old values
            function fillAddressBlock(block, address, index) {
                block.querySelector(`select[name="addresses[${index}][type]"]`).value = address.type;
                block.querySelector(`textarea[name="addresses[${index}][street_address]"]`).value = address
                    .street_address;
                block.querySelector(`select[name="addresses[${index}][country_id]"]`).value = address.country_id;
                block.querySelector(`input[name="addresses[${index}][city]"]`).value = address.city;
                block.querySelector(`input[name="addresses[${index}][pincode]"]`).value = address.pincode;
            }

            // Handle removal of dynamic fields
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-field') || e.target.closest('.remove-field')) {
                    const button = e.target.classList.contains('remove-field') ? e.target : e.target
                        .closest('.remove-field');
                    button.closest('.input-group').remove();
                }
                if (e.target.classList.contains('remove-block') || e.target.closest('.remove-block')) {
                    const button = e.target.classList.contains('remove-block') ? e.target : e.target
                        .closest('.remove-block');
                    button.closest('.address-block').remove();
                }
            });

            // Real-time validation function
            function validateField(field) {
                const isValid = field.checkValidity();
                field.classList.toggle('is-invalid', !isValid);
                field.classList.toggle('is-valid', isValid);

                // Remove existing feedback
                const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
                if (existingFeedback) {
                    existingFeedback.remove();
                }

                if (!isValid) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';

                    if (field.validity.valueMissing) {
                        feedback.textContent = 'This field is required';
                    } else if (field.validity.typeMismatch) {
                        if (field.type === 'email') {
                            feedback.textContent = 'Please enter a valid email address';
                        } else if (field.type === 'tel') {
                            feedback.textContent = 'Please enter a valid phone number';
                        }
                    } else if (field.validity.patternMismatch) {
                        feedback.textContent = field.title || 'Please match the requested format';
                    }

                    field.parentNode.appendChild(feedback);
                }

                return isValid;
            }

            // Attach validation listeners to a field
            function attachValidationListeners(field) {
                field.addEventListener('blur', () => {
                    validateField(field);
                });

                let timeout;
                field.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        validateField(field);
                    }, 500);
                });
            }

            // Initialize validation for all form fields
            const form = document.getElementById('clientForm');
            form.querySelectorAll('input, select, textarea').forEach(field => {
                attachValidationListeners(field);
            });

            // Handle form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Get active tab
                const activeTab = document.querySelector('.nav-link.active').getAttribute('data-bs-target')
                    .replace('#', '');

                // Update active tab input
                let activeTabInput = form.querySelector('input[name="active_tab"]');
                if (!activeTabInput) {
                    activeTabInput = document.createElement('input');
                    activeTabInput.type = 'hidden';
                    activeTabInput.name = 'active_tab';
                    form.appendChild(activeTabInput);
                }
                activeTabInput.value = activeTab;

                // Validate all fields
                let isValid = true;
                let firstInvalidField = null;

                form.querySelectorAll('input, select, textarea').forEach(field => {
                    if (!validateField(field)) {
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                        }
                    }
                });

                if (!isValid && firstInvalidField) {
                    // Switch to tab containing first invalid field
                    const fieldTab = firstInvalidField.closest('.tab-pane').id;
                    const tabButton = document.querySelector(`[data-bs-target="#${fieldTab}"]`);
                    if (tabButton) {
                        const tab = new bootstrap.Tab(tabButton);
                        tab.show();
                    }

                    // Scroll to and focus the invalid field
                    firstInvalidField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstInvalidField.focus();
                    return;
                }

                if (isValid) {
                    // Sync WYSIWYG editor
                    if (typeof tinymce !== 'undefined') {
                        tinymce.triggerSave();
                    }

                    // Store form data backup

                    const formData = new FormData(form);
                    const formDataObj = {};
                    formData.forEach((value, key) => {
                        formDataObj[key] = value;
                    });
                    localStorage.setItem('formBackup', JSON.stringify(formDataObj));

                    this.submit();
                }
            });

            // Initialize tab from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('active_tab') || 'general';
            const tabToShow = document.querySelector(`button[data-bs-target="#${activeTab}"]`);
            if (tabToShow) {
                const tab = new bootstrap.Tab(tabToShow);
                tab.show();
            }

            // Restore form data from localStorage if it exists
            const savedFormData = localStorage.getItem('formBackup');
            if (savedFormData) {
                try {
                    const formDataObj = JSON.parse(savedFormData);
                    Object.entries(formDataObj).forEach(([key, value]) => {
                        const field = form.querySelector(`[name="${key}"]`);
                        if (field) {
                            field.value = value;
                            // Trigger change event for Select2 fields
                            if ($(field).hasClass('select2-hidden-accessible')) {
                                $(field).trigger('change');
                            }
                        }
                    });
                    // Clear the backup after restoration
                    localStorage.removeItem('formBackup');
                } catch (error) {
                    console.error('Error restoring form data:', error);
                    localStorage.removeItem('formBackup');
                }
            }
        });
    </script>
@endpush
