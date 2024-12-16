@extends('layout.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="plansForm" action="{{ route(getPanelRoutes('plans.store')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type='hidden' name='id' value="{{ $plan->id }}" />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('plans.Update Plan') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('plans.Update Plan') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="plansTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('plans.General') }}
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="address-tab" data-bs-toggle="tab"
                                        data-bs-target="#features" type="button" role="tab">
                                        {{ __('plans.Features') }}
                                    </button>
                                </li>



                            </ul>


                            <div class="tab-content mt-4" id="plansTabContent">
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="planName" class="custom-class" required>
                                                {{ __('plans.Plan Name') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" class="custom-class"
                                                id="planName" name="name" placeholder="{{ __('Premium') }}"
                                                value="{{ $plan->name }}" required />

                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="planDesc" class="custom-class" required>
                                                {{ __('plans.Plan Desc') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" class="custom-class"
                                                id="planDesc" name="desc" placeholder="{{ __('For Startups') }}"
                                                value="{{ $plan->desc }}" required />

                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="planPrice" class="custom-class" required>
                                                {{ __('plans.Plan Price') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append
                                                prepend="{{ getSettingValue('Panel Currency Symbol') }}"
                                                append="{{ getSettingValue('Panel Currency Code') }}" type="number"
                                                step="0.001" class="custom-class" id="planPrice" name="price"
                                                placeholder="{{ __('129.99') }}" value="{{ $plan->price }}" required />

                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="planOfferPrice" class="custom-class"
                                                required>
                                                {{ __('plans.Plan Offer Price') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append
                                                prepend="{{ getSettingValue('Panel Currency Symbol') }}"
                                                append="{{ getSettingValue('Panel Currency Code') }}" type="number"
                                                step="0.001" class="custom-class" id="planOfferPrice" name="offer_price"
                                                placeholder="{{ __('99.99') }}" value="{{ $plan->offer_price }}"
                                                required />

                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="planBillingCycle" class="custom-class"
                                                required>
                                                {{ __('plans.Select Billing Cycle') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('billing_cycle') is-invalid @enderror"
                                                    name="billing_cycle" id="billing_cycle">
                                                    @if (PLANS_BILLING_CYCLES['BILLINGS'])
                                                        @foreach (PLANS_BILLING_CYCLES['BILLINGS'] as $billingcycle)
                                                            <option value="{{ $billingcycle }}"
                                                                {{ $plan->billing_cycle == $billingcycle ? 'selected' : '' }}>
                                                                {{ $billingcycle }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No billing cycle available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="country_idError">
                                                    @error('billing_cycle')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>
                                        <p class="offset-md-4 font-12 my-2 text-secondary">Unlimited means <span
                                                class="text-success">one time cost</span>, no billing to this company.</p>
                                    </div>
                                    <hr>
                                    <p class="alert alert-secondary">Please add <span class="text-success">Plans
                                            Features</span> on features tabs, default to 10</p>
                                </div>
                                <div class="tab-pane fade" id="features" role="tabpanel">
                                    @foreach (PLANS_FEATURES as $pf)
                                        <div class="row mb-4 align-items-center">
                                            <div class="col-lg-4">
                                                <x-form-components.input-label for="{{ $pf }}"
                                                    class="custom-class" required>
                                                    {{ ucwords(strtolower($pf)) }} Create:
                                                </x-form-components.input-label>
                                            </div>
                                            <div class="col-lg-8">
                                                @php
                                                    $pfs = strtolower($pf);
                                                    $featureValue =
                                                        $plan->planFeatures->firstWhere('module_name', strtolower($pfs))
                                                            ->value ?? 0;
                                                @endphp
                                                <x-form-components.input-group type="number" class="custom-class"
                                                    id="{{ $pfs }}" name="features.{{ $pfs }}"
                                                    placeholder="{{ __('10') }}" value="{{ $featureValue }}"
                                                    min="-1" required />


                                            </div>
                                            <p class="offset-lg-4 font-12 my-2 text-secondary">
                                                <span class="text-success"> -1</span> For Unlimited. || <span
                                                    class="text-success">0</span> Means this feature is disable
                                            </p>
                                        </div>
                                    @endforeach



                                </div>
                            </div>




                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add custom tab validation or handling
            const tabs = document.querySelectorAll('#plansTab .nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // You can add custom logic here if needed
                    console.log(`Switched to tab: ${this.textContent}`);
                });
            });
        });
    </script>
@endpush
