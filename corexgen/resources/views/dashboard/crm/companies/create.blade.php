@extends('layout.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-9">
            <div class="card stretch stretch-full">
           

                <form id="userForm" action="{{ route(getPanelRoutes('companies.store')) }}" method="POST">
                    @csrf
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <p class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('companies.Create New Company') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i>  <span>{{ __('companies.Create Company') }}</span>
                            </button>
                        </div>
                
                        <!-- Full Name Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="nameName" class="fw-semibold">{{ __('companies.Full Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                  
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="nameName" 
                                           name="name" 
                                           placeholder="{{ __('John Doe') }}"
                                           value="{{ old('name') }}"
                                           required>
                                    <div class="invalid-feedback" id="nameNameError">
                                        @error('name')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Email Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="emailName" class="fw-semibold">{{ __('companies.Email') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                   
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="emailName" 
                                           name="email" 
                                           required
                                           placeholder="{{ __('john@doe.com') }}"
                                           value="{{ old('email') }}">
                                    <div class="invalid-feedback" id="emailNameError">
                                        @error('email')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                
                     <!-- Phone Field -->
                     <div class="row mb-4 align-items-center">
                        <div class="col-lg-4">
                            <label for="phone" class="fw-semibold">{{ __('companies.Phone') }}: <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-lg-8">
                            <div class="input-group">
                               
                                <input type="number" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       required
                                       placeholder="{{ __('john@doe.com') }}"
                                       value="{{ old('phone') }}">
                                <div class="invalid-feedback" id="phoneError">
                                    @error('phone')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
            
                
                        <!-- Password Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="passName" class="fw-semibold">{{ __('companies.Password') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                 
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="passName" 
                                           name="password" 
                                           placeholder="{{ __('********') }}"
                                           required>
                                    <div class="invalid-feedback" id="passNameError">
                                        @error('password')
                                            {{ $message }}
                                        @enderror
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
