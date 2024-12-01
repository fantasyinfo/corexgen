@extends('layout.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-9">
            <div class="card stretch stretch-full">
           

                <form id="userForm" action="{{ route(getPanelRoutes('users.store')) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type='hidden' name='id' value='{{$user["id"]}}' />
                    <input type='hidden' name='email' value='{{$user["email"]}}' />
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('users.Update User') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </h5>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-plus me-2"></i> <span>{{ __('users.Update User') }}</span>
                            </button>
                        </div>
                
                        <!-- Full Name Field -->
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="nameName" class="mb-2 fw-semibold">{{ __('users.Full Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                 
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="nameName" 
                                           name="name" 
                                           placeholder="{{ __('John Doe') }}"
                                           value="{{ $user->name }}"
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
                                <label for="emailName" class="mb-2 fw-semibold">{{ __('users.Email') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                   
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="emailName" 
                                   
                                           disabled
                                           placeholder="{{ __('john@doe.com') }}"
                                           value="{{ $user->email }}">
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
                                <label for="role_id" class="mb-2 fw-semibold">{{ __('users.Select Role') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                          
                                    <select class="form-control searchSelectBox @error('role_id') is-invalid @enderror" name="role_id" id="role_id">
                                        @if($roles && $roles->isNotEmpty())
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
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
                
                      
                    </div>
                </form>
                
            </div>
        </div>
    </div>
</div>
    @endsection
