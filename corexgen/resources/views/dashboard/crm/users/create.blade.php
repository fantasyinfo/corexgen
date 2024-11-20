@extends('layout.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
           

                <form id="userForm" action="{{ route('crm.users.store') }}" method="POST">
                    @csrf
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('Create new user') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('Please add correct information') }}</span>
                            </h5>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-plus me-2"></i> <span>{{ __('Create User') }}</span>
                            </button>
                        </div>
                
                        <!-- Full Name Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="nameName" class="fw-semibold">{{ __('Full Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-user"></i></div>
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
                                <label for="emailName" class="fw-semibold">{{ __('Email') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fa-regular fa-envelope"></i></div>
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
                
                        <!-- Role Selection Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="role_id" class="fw-semibold">{{ __('Select Role') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fa-regular fa-user"></i></div>
                                    <select class="form-control select2-hidden-accessible @error('role_id') is-invalid @enderror" name="role_id" id="role_id">
                                        @if($roles && $roles->isNotEmpty())
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option disabled>No roles available</option>
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="role_idError">
                                        @error('role_id')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                
                        <!-- Password Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="passName" class="fw-semibold">{{ __('Password') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fa-solid fa-lock"></i></div>
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
