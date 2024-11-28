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
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span>{{ __('companies.Create Company') }}</span>
                            </button>
                        </div>

                        <!-- Bootstrap Tabs -->
                        <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                    {{ __('companies.General') }}
                                </button>
                            </li>
                       
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">
                                    {{ __('companies.Address') }}
                                </button>
                            </li>
                       
                          
                    
                        </ul>

                  
                        <div class="tab-content mt-4" id="companyTabsContent">
                            <!-- General Information Tab -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="companyName" class="fw-semibold">{{ __('companies.Company Name') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" 
                                               class="form-control @error('cname') is-invalid @enderror" 
                                               id="companyName" 
                                               name="cname" 
                                               placeholder="{{ __('Enter Company Name') }}"
                                               value="{{ old('cname') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('cname')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="primaryContactName" class="fw-semibold">{{ __('companies.Full Name') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="primaryContactName" 
                                               name="name" 
                                               placeholder="{{ __('John Doe') }}"
                                               value="{{ old('name') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="emailName" class="fw-semibold">{{ __('companies.Email') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="emailName" 
                                               name="email" 
                                               placeholder="{{ __('john@doe.com') }}"
                                               value="{{ old('email') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="phone" class="fw-semibold">{{ __('companies.Phone') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               placeholder="{{ __('9876543210') }}"
                                               value="{{ old('phone') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('phone')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="password" class="fw-semibold">{{ __('companies.Password') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               placeholder="{{ __('*********') }}"
                                               value="{{ old('password') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('password')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="password" class="fw-semibold">{{ __('companies.Select Plan') }}: <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               placeholder="{{ __('*********') }}"
                                               value="{{ old('password') }}"
                                               required>
                                        <div class="invalid-feedback">
                                            @error('password')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Addresses Tab -->
                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="registeredAddress" class="fw-semibold">{{ __('address.Address') }}:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <textarea class="form-control" 
                                                  id="registeredAddress" 
                                                  name="address" 
                                                  rows="3" 
                                                  placeholder="{{ __('Enter Registered Address') }}">{{ old('address') }}</textarea>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="pincode" class="fw-semibold">{{ __('address.Pincode') }}:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" 
                                               class="form-control" 
                                               id="pincode" 
                                               name="pincode" 
                                               placeholder="{{ __('Enter Pincode') }}"
                                               value="{{ old('pincode') }}">
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="city" class="fw-semibold">{{ __('address.City') }}:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <input type="text" 
                                               class="form-control" 
                                               id="city" 
                                               name="city" 
                                               placeholder="{{ __('Enter City') }}"
                                               value="{{ old('city') }}">
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-4">
                                        <label for="country" class="fw-semibold">{{ __('address.Country') }}:</label>
                                    </div>
                                    <div class="col-lg-8">
                                        <select class="form-select" id="country" name="country">
                                            <option value="">{{ __('Select Country') }}</option>
                                            <!-- Add country options here -->
                                            <option value="us">United States</option>
                                            <option value="uk">United Kingdom</option>
                                            <!-- Add more countries as needed -->
                                        </select>
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
</script>
@endpush
@endsection