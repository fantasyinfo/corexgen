@extends('layout.app')

@section('content')
    @php
        // prePrintR($user);
    @endphp
    <div class="container py-4">
        <div class="row g-4">
            <!-- Profile Details Section -->
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-header bg-primary text-white">
                        <h3 class="my-0">
                            <i class="fas fa-user-circle me-2"></i>{{ __('users.Profile Details') }}
                        </h3>
                    </div>

                    <div class="card-body p-4">
                        <!-- Profile Avatar Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">

                                <x-form-components.profile-avatar :id="'avatarPreview'" :hw="80" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" />

                                <label for="avatarInput"
                                    class="position-absolute bottom-0 end-0 rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 32px; height: 32px; background: var(--primary-color); cursor: pointer;">
                                    <i class="fas fa-camera text-white fs-6"></i>
                                </label>
                            </div>
                        </div>

                        <form id="profileForm" method="POST" action="{{ route(getPanelRoutes('users.update')) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type='hidden' name='id' value='{{ $user['id'] }}'>
                            <input type='hidden' name='email' value='{{ $user['email'] }}'>
                            <input type='hidden' name='role_id' value='{{ @$user->role->id }}'>
                            <input type='hidden' name='is_profile' value='true'>

                            <!-- Hidden Avatar Input -->
                            <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/*">

                            <!-- Name Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="nameName">
                                    {{ __('users.Full Name') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" id="nameName" name="name"
                                    placeholder="{{ __('John Doe') }}" value="{{ old('name', $user->name) }}" required />
                            </div>

                            <!-- Email Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="email">
                                    {{ __('users.Email') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="email" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" disabled />
                            </div>

                            <!-- Role Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="role">
                                    {{ __('users.Role') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" id="role" name="role"
                                    value="{{ @$user->role->role_name ?? 'Owner' }}" disabled />
                            </div>

                            <!-- Address Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="compnayAddressStreet">
                                    {{ __('address.Address') }}
                                </x-form-components.input-label>

                                <x-form-components.textarea-group name="address.street_address" id="compnayAddressStreet"
                                    placeholder="Enter Registered Street Address"
                                    value="{{ old('address.street_address', @$user->addresses->street_address) }}" />
                            </div>

                            <!-- Country Selection -->
                            <div class="mb-4">
                                <x-form-components.input-label for="country_id">
                                    {{ __('address.Country') }}
                                </x-form-components.input-label>

                                <select
                                    class="form-select searchSelectBox @error('address.country_id') is-invalid @enderror"
                                    name="address.country_id" id="country_id"
                                    style="background-color: var(--input-bg); border-color: var(--input-border);">
                                    <option value="">{{ __('Select Country') }}</option>
                                    @foreach ($country as $c)
                                        <option value="{{ $c->id }}"
                                            {{ @$user->addresses->country_id == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('address.country_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="compnayAddressCity">
                                    {{ __('address.City') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" name="address.city_name"
                                    id="compnayAddressCity" placeholder="City Name"
                                    value="{{ old('address.city_name', @$user->addresses->city->city_name) }}" />
                            </div>

                            <!-- Pincode Field -->
                            <div class="mb-4">
                                <x-form-components.input-label for="compnayAddressPincode">
                                    {{ __('address.Pincode') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" name="address.pincode"
                                    id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                    value="{{ old('address.pincode', @$user->addresses->postal_code) }}" />
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>{{ __('Update Profile') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="col-lg-6">
                <div class="card border-0">
                    <div class="card-header" style="background-color: var(--info-color);">
                        <h3 class="my-0 text-white">
                            <i class="fas fa-lock me-2"></i>{{ __('users.Change Password') }}
                        </h3>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route(getPanelRoutes('users.updatePassword')) }}">
                            @csrf
                            <input type='hidden' name='id' value='{{ $user['id'] }}'>

                            <!-- Old Password -->
                            <div class="mb-4">
                                <x-form-components.input-label for="oldPass">
                                    {{ __('users.Old Password') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="password" id="oldPass" name="old_password"
                                    required />
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                                <x-form-components.input-label for="newPass">
                                    {{ __('users.New Password') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="password" id="newPass" name="password"
                                    placeholder="Must include 8+ characters, uppercase, lowercase, numbers, and special characters"
                                    required />
                                <span class="text-muted font-12">
                                    Password must:
                                    <ul>
                                        <li>Be at least 8 characters long</li>
                                        <li>Contain at least one uppercase letter</li>
                                        <li>Contain at least one lowercase letter</li>
                                        <li>Contain at least one number</li>
                                        <li>Contain at least one special character (e.g., @, $, !, %, *, ?, &)</li>
                                    </ul>
                                </span>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <x-form-components.input-label for="confirmPass">
                                    {{ __('users.Confirm Password') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="password" id="confirmPass"
                                    name="password_confirmation" placeholder="Re-enter your new password" required />
                            </div>

                            <button type="submit" class="btn w-100 text-white"
                                style="background-color: var(--info-color);">
                                <i class="fas fa-key me-2"></i>{{ __('Change Password') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Avatar preview functionality
            document.getElementById('avatarInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('avatarPreview').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Initialize select2 if you're using it
            $(document).ready(function() {
                $('.searchSelectBox').select2({
                    theme: 'bootstrap-5'
                });
            });
        </script>
    @endpush
@endsection
