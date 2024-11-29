@extends('layout.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="userForm" action="{{ route(getPanelRoutes('plans.store')) }}" method="POST">
                        @csrf
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('plans.Create Plan') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('plans.Create Plan') }}</span>
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
                                            <label for="nameName" class="mb-2 fw-semibold">{{ __('plans.Plan Name') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="text"
                                                    class="form-control @error('name') is-invalid @enderror" id="nameName"
                                                    name="name" placeholder="{{ __('Premium') }}"
                                                    value="{{ old('name') }}" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('name')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="nameName" class="mb-2 fw-semibold">{{ __('plans.Plan Desc') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="text"
                                                    class="form-control @error('desc') is-invalid @enderror" id="nameName"
                                                    name="desc" placeholder="{{ __('For Startups') }}"
                                                    value="{{ old('desc') }}" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('desc')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="nameName" class="mb-2  fw-semibold">{{ __('plans.Plan Price') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="number"
                                                    class="form-control @error('price') is-invalid @enderror" id="nameName"
                                                    name="price" placeholder="{{ __('129.99') }}"
                                                    value="{{ old('price') }}" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('price')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="nameName"
                                                class="mb-2  fw-semibold">{{ __('plans.Plan Offer Price') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="number"
                                                    class="form-control @error('offer_price') is-invalid @enderror"
                                                    id="nameName" name="offer_price" placeholder="{{ __('99.99') }}"
                                                    value="{{ old('offer_price') }}" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('offer_price')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="country_id"
                                                class="mb-2 fw-semibold">{{ __('plans.Select Billing Cycle') }}: <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox select2-hidden-accessible @error('billing_cycle') is-invalid @enderror"
                                                    name="billing_cycle" id="billing_cycle">
                                                    @if (PLANS_BILLING_CYCLES['BILLINGS'])
                                                        @foreach (PLANS_BILLING_CYCLES['BILLINGS'] as $billingcycle)
                                                            <option value="{{ $billingcycle }}"
                                                                {{ old('billing_cycle') == $billingcycle ? 'selected' : '' }}>
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
                                </div>
                                <div class="tab-pane fade" id="features" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="nameName" class="mb-2 fw-semibold">{{ __('plans.Users Limit') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="number"
                                                    class="form-control @error('users_limit') is-invalid @enderror"
                                                    id="nameName" name="users_limit" placeholder="{{ __('10') }}"
                                                    value="10" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('users_limit')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <p class="offset-lg-4 font-12 my-2 text-secondary">For Unlimited typoe <span
                                                class="text-success"> -1</span>.</p>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <label for="nameName" class="mb-2 fw-semibold">{{ __('plans.Roles Limit') }}:
                                                <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <input type="number"
                                                    class="form-control @error('roles_limit') is-invalid @enderror"
                                                    id="nameName" name="roles_limit" placeholder="{{ __('10') }}"
                                                    value="10" required>
                                                <div class="invalid-feedback" id="nameNameError">
                                                    @error('roles_limit')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <p class="offset-lg-4 font-12 my-2 text-secondary">For Unlimited typoe <span
                                                class="text-success"> -1</span>.</p>
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
