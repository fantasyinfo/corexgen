@extends('layout.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="roleForm" action="{{ route(getPanelRoutes('paymentGateway.update')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type='hidden' name='id' value='{{ $gateway['id'] }}' />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('paymentgateway.Update Gateway') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('paymentgateway.Update Gateway Settings') }}</span>
                                </button>
                            </div>

                            <div class="row mb-4">
                                <img src="{{ asset('img/gateway/' . $gateway['logo']) }}" class='gateway_logo_img' />
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pName" class="custom-class" required>
                                        {{ __('paymentgateway.Name') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" disabled="true" class="custom-class"
                                        name="name" id="pName" placeholder="{{ __('Paypal') }}"
                                        value="{{ old('name',$gateway['name']) }}" />
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pKey" class="custom-class" required>
                                        {{ __('paymentgateway.Config Key') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text"  class="custom-class"
                                        name="config_key" id="pKey" placeholder="{{ __('Paypal') }}"
                                        value="{{ old('config_key',$gateway['config_key']) }}" />
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pValue" class="custom-class" required>
                                        {{ __('paymentgateway.Config Value') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text"  class="custom-class"
                                        name="config_value" id="pValue" placeholder="{{ __('Paypal') }}"
                                        value="{{ old('config_value',$gateway['config_value']) }}" />
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pMode" class="custom-class" required>
                                        {{ __('paymentgateway.Mode') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                 
                                    <select name="mode" class="form-control searchSelectBox @error('mode') is-invalid @enderror" >
                                        <option value="LIVE"  {{ 'LIVE' == $gateway->mode ? 'selected' : '' }}>LIVE</option>
                                        <option value="TEST"  {{ 'TEST' == $gateway->mode ? 'selected' : '' }}>TEST</option>
                                    </select>
                                    <div class="invalid-feedback" id="modeError">
                                        @error('mode')
                                            {{ $message }}
                                        @enderror
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
