@extends('layout.app')

@section('content')
@php
    // prePrintR($user->toArray());
@endphp

    <div class="container ">
        <div class="row ">
            <div class="col-lg-6">
                <div class="card  border-0 rounded-lg">
                    <div class="card-header  d-flex justify-content-between align-items-center">
                        <h3 class=" my-0">
                            <i class="fas fa-user-circle me-2"></i>{{ __('users.Profile Details') }}
                        </h3>
                    
                    </div>

                    <div class="card-body">
                        <form id="profileForm" method="POST" action="{{ route(getPanelRoutes('users.store')) }}">
                            @csrf
                            @method('PUT')
                            <input type='hidden' name='id' value='{{ $user['id'] }}' />
                            <input type='hidden' name='email' value='{{ $user['email'] }}' />
                            <input type='hidden' name='role_id' value='{{ @$user->role->id }}' />
                            <input type='hidden' name='is_profile' value='true' />

                            <div class="row mb-3">
                                <x-form-components.input-label for="nameName" class="col-md-4 col-form-label">
                                    {{ __('users.Full Name') }}
                                </x-form-components.input-label>


                                <x-form-components.input-group type="text" class="form-control editable-field"
                                    id="nameName" name="name" placeholder="{{ __('John Doe') }}"
                                    value="{{ old('name',$user->name) }}"  required />

                            </div>

                            <div class="row mb-3">
                                <x-form-components.input-label for="email" class="col-md-4 col-form-label">
                                    {{ __('users.Email') }}
                                </x-form-components.input-label>


                                <x-form-components.input-group type="email" class="form-control" id="email"
                                    name="email" placeholder="{{ __('user@email.com') }}" value="{{old('email',$user->email) }}"
                                    disabled />

                            </div>
                         
                            <div class="row mb-3">
                                <x-form-components.input-label for="role" class="col-md-4 col-form-label">
                                    {{ __('users.Role') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" class="form-control"
                                    id="role" name="role" placeholder="{{ __('Manager/Employee') }}"
                                    value="{{ @$user->role->role_name ?? 'Owner' }}" disabled />

                            </div>

                            <div class="row mb-4 align-items-center">
                               
                                    <x-form-components.input-label for="compnayAddressStreet"
                                        class="custom-class">
                                        {{ __('address.Address') }}
                                    </x-form-components.input-label>
                             
                                    <x-form-components.textarea-group name="address.street_address"
                                        id="compnayAddressStreet" placeholder="Enter Registered Street Address"
                                        value="{{  old('address.street_address',@$user->addresses->street_address)  }}" class="custom-class" />
                                     

                               
                            </div>
                            <div class="row mb-4 align-items-center">
                               
                                    <x-form-components.input-label for="compnayAddressCountry"
                                        class="custom-class">
                                        {{ __('address.Country') }}
                                    </x-form-components.input-label>
                               
                                    <div class="input-group">

                                        <select
                                            class="form-control searchSelectBox  @error('address.country_id') is-invalid @enderror"
                                            name="address.country_id" id="country_id">
                                            <option value="0"> ----- Select Country ---------- </option>
                                            @if ($country)
                                                @foreach ($country as $country)
                                                    <option value="{{ $country->id }}"
                                                        {{ @$user->addresses->country_id == $country->id ? 'selected' : '' }}>
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

                            <div class="row mb-4 align-items-center">
                             
                                    <x-form-components.input-label for="compnayAddressCity" class="custom-class">
                                        {{ __('address.City') }}
                                    </x-form-components.input-label>
                                    <x-form-components.input-group name="address.city_name"
                                    id="compnayAddressStreet" placeholder="City Name"
                                    value="{{ old('address.city_name',@$user->addresses->city->city_name) }}" class="custom-class" />
                            
                               
                            </div>
                            <div class="row mb-4 align-items-center">
                               
                                    <x-form-components.input-label for="compnayAddressPincode"
                                        class="custom-class">
                                        {{ __('address.Pincode') }}
                                    </x-form-components.input-label>
                         
                              
                                    <x-form-components.input-group type="text" name="address.pincode"
                                        id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                        value="{{ old('address.pincode',@$user->addresses->postal_code) }}" class="custom-class" />

                               
                            </div>

                            <div id="saveButtonContainer" class="row mb-0" >
                                <div class="">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>{{ __('Save Changes') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
