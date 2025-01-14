@extends('layout.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">

                    <form id="roleForm" action="{{ route(getPanelRoutes('paymentGateway.update')) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Hidden field to identify which gateway record we're updating -->
                        <input type="hidden" name="id" value="{{ $gateway->id }}" />

                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">
                                        {{ __('paymentgateway.Update Gateway') }}
                                    </span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <span>
                                        {{ __('paymentgateway.Update Gateway Settings') }}
                                    </span>
                                </button>
                            </div>

                            <div class="row mb-4">
                                <img src="{{ asset('img/gateway/' . $gateway->logo) }}" class="gateway_logo_img" />
                            </div>

                            @php
                                // If the user is a tenant, we pull data from the PaymentGateway model itself.
                                // If the user is NOT tenant (company user), we look for the first (and presumably only) PaymentGatewaySettings record for this company.
                                if ($isTenant) {
                                    $currentConfigKey   = $gateway->config_key;
                                    $currentConfigValue = $gateway->config_value;
                                    $currentMode        = $gateway->mode;
                                } else {
                                    // Grab the first PaymentGatewaySettings record (if it exists) for the current company
                                    $companySetting = $gateway->paymentGatewaySettings->first();
                                    $currentConfigKey   = $companySetting?->config_key   ?? '';
                                    $currentConfigValue = $companySetting?->config_value ?? '';
                                    $currentMode        = $companySetting?->mode         ?? 'TEST'; // default to LIVE or TEST as needed
                                }
                            @endphp

                            <!-- Name (tenant or company user sees the same; but only tenant can truly edit gateway name, so we keep it disabled) -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pName" class="custom-class" required>
                                        {{ __('paymentgateway.Name') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group
                                        type="text"
                                        name="name"
                                        id="pName"
                                        placeholder="{{ __('Paypal') }}"
                                        value="{{ old('name', $gateway->name) }}"
                                        class="custom-class"
                                        disabled="true" />
                                </div>
                            </div>

                            <!-- Config Key -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pKey" class="custom-class" required>
                                        {{ __('paymentgateway.Config Key') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group
                                        type="text"
                                        name="config_key"
                                        id="pKey"
                                        placeholder="{{ __('Config Key') }}"
                                        value="{{ old('config_key', $currentConfigKey) }}"
                                        class="custom-class" />
                                </div>
                            </div>

                            <!-- Config Value -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pValue" class="custom-class" required>
                                        {{ __('paymentgateway.Config Value') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group
                                        type="text"
                                        name="config_value"
                                        id="pValue"
                                        placeholder="{{ __('Config Value / Secret key') }}"
                                        value="{{ old('config_value', $currentConfigValue) }}"
                                        class="custom-class" />
                                </div>
                            </div>

                            <!-- Mode (LIVE/TEST) -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="pMode" class="custom-class" required>
                                        {{ __('paymentgateway.Mode') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select
                                        name="mode"
                                        class="form-control searchSelectBox @error('mode') is-invalid @enderror"
                                        id="pMode"
                                    >
                                        <option value="LIVE" {{ $currentMode == 'LIVE' ? 'selected' : '' }}>
                                            LIVE
                                        </option>
                                        <option value="TEST" {{ $currentMode == 'TEST' ? 'selected' : '' }}>
                                            TEST
                                        </option>
                                    </select>
                                    <div class="invalid-feedback" id="modeError">
                                        @error('mode')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- /Mode -->

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
