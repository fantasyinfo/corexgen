@extends('layout.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="userForm" action="{{ route(getPanelRoutes('users.store')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type='hidden' name='id' value='{{ $user['id'] }}' />
                        <input type='hidden' name='email' value='{{ $user['email'] }}' />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('users.Update User') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </h5>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-plus me-2"></i> <span>{{ __('users.Update User') }}</span>
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
                                        name="name" placeholder="{{ __('John Doe') }}" value="{{ $user->name }}"
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
                                        name="email" placeholder="{{ __('john@email.com') }}"
                                        value="{{ $user->email }}" required />

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

                                        <select class="form-control searchSelectBox @error('role_id') is-invalid @enderror"
                                            name="role_id" id="role_id">
                                            @if ($roles && $roles->isNotEmpty())
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}"
                                                        {{ $user->role_id == $role->id ? 'selected' : '' }}>
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
