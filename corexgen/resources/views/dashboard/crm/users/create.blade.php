@extends('layout.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="userForm" action="{{ route(getPanelRoutes('users.store')) }}" method="POST">
                        @csrf
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('users.Create New User') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('users.Create User') }}</span>
                                </button>
                            </div>

                            <!-- Full Name Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="nameName" class="custom-class" required>
                                        {{ __('users.Full Name') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="nameName"
                                        name="name" placeholder="{{ __('John Doe') }}" value="{{ old('name') }}"
                                        required />

                                </div>
                            </div>

                            <!-- Email Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="emailName" class="custom-class" required>
                                        {{ __('users.Email') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="email" class="custom-class" id="emailName"
                                        name="email" placeholder="{{ __('john@email.com') }}" value="{{ old('email') }}"
                                        required />

                                </div>
                            </div>

                            <!-- Role Selection Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="role_id" class="custom-class" required>
                                        {{ __('users.Select Role') }}
                                    </x-form-components.input-label>

                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">

                                        <select class="form-control searchSelectBox  @error('role_id') is-invalid @enderror"
                                            name="role_id" id="role_id">
                                            @if ($roles && $roles->isNotEmpty())
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}"
                                                        {{ old('role_id') == $role->id ? 'selected' : '' }}>
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

                                    <x-form-components.input-label for="passName" class="custom-class" required>
                                        {{ __('users.Password') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="password" class="custom-class" id="passName"
                                        name="password" placeholder="{{ __('********') }}" value="{{ old('password') }}"
                                        required />

                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
