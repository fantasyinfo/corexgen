@extends('layout.app')

@section('content')
    @php

        $type = null;
        $id = null;
        $refrer = null;
        if (isset($_GET['type']) && isset($_GET['id']) && isset($_GET['refrer'])) {
            $type = trim($_GET['type']);
            $id = trim($_GET['id']);
            $refrer = trim($_GET['refrer']);
        }
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">


                    <form id="productsForm" action="{{ route(getPanelRoutes('products_services.store')) }}" method="POST">
                        @csrf
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('products.Create Product') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('products.Create Product') }}</span>
                                </button>
                            </div>

                            <!-- Bootstrap Tabs -->
                            <ul class="nav nav-tabs" id="companyTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                        data-bs-target="#general" type="button" role="tab">
                                        {{ __('products.General') }}
                                    </button>
                                </li>


                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="custom-fields-tab" data-bs-toggle="tab"
                                            data-bs-target="#custom-fields" type="button" role="tab">
                                            {{ __('customfields.Custom Fields') }}
                                        </button>
                                    </li>
                                @endif
                            </ul>

                            <div class="tab-content mt-4" id="companyTabsContent">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="nameName" class="custom-class" required>
                                                {{ __('products.Product Type') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <select name="type" id="type" class="form-select">
                                                <option value="Product" {{ old('type') == 'Product' ? 'selected' : '' }}
                                                    {{ $type == 'Product' ? 'selected' : '' }}>Product
                                                </option>
                                                <option value="Service" {{ old('type') == 'Service' ? 'selected' : '' }}
                                                    {{ $type == 'Service' ? 'selected' : '' }}>Services</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Full Name Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="nameName" class="custom-class" required>
                                                {{ __('products.Title') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="text" class="custom-class"
                                                id="nameName" name="title" placeholder="{{ __('Product title...') }}"
                                                value="{{ old('title') }}" required />

                                        </div>
                                    </div>



                                    <!-- Category Selection Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="role_id" class="custom-class">
                                                {{ __('products.Select Category') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('cgt_id') is-invalid @enderror"
                                                    name="cgt_id" id="cgt_id">
                                                    <option value="">No Category </option>
                                                    @if ($categories && $categories->isNotEmpty())
                                                        @foreach ($categories as $cat)
                                                            <option value="{{ $cat->id }}"
                                                                {{ old('cgt_id') == $cat->id ? 'selected' : '' }}>
                                                                {{ $cat->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No categories available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="role_idError">
                                                    @error('cgt_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="role_id" class="custom-class">
                                                {{ __('products.Description') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <x-form-components.textarea-group class="custom-class" id="nameName"
                                                    name="description" placeholder="{{ __('Product description...') }}"
                                                    value="{{ old('description') }}" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Full Name Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="nameName" class="custom-class" required>
                                                {{ __('products.Rate') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group-prepend-append type="number"
                                                class="custom-class" prepend="{{ getSettingValue('Currency Symbol') }}"
                                                append="{{ getSettingValue('Currency Code') }}" id="nameName"
                                                name="rate" step="0.001" placeholder="{{ __('Product price...') }}"
                                                value="{{ old('rate') }}" required />

                                        </div>
                                    </div>

                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">

                                            <x-form-components.input-label for="nameName" class="custom-class" required>
                                                {{ __('products.Unit') }}
                                            </x-form-components.input-label>
                                        </div>
                                        <div class="col-lg-8">
                                            <x-form-components.input-group type="number" class="custom-class"
                                                id="nameName" name="unit" placeholder="{{ __('Product qty...') }}"
                                                value="{{ old('unit') }}" required />

                                        </div>
                                    </div>

                                    <!-- Tax Selection Field -->
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-lg-4">
                                            <x-form-components.input-label for="role_id" class="custom-class">
                                                {{ __('products.Select Tax') }}
                                            </x-form-components.input-label>

                                        </div>
                                        <div class="col-lg-8">
                                            <div class="input-group">

                                                <select
                                                    class="form-control searchSelectBox  @error('tax_id') is-invalid @enderror"
                                                    name="tax_id" id="tax_id">
                                                    <option value="">No tax </option>
                                                    @if ($taxes && $taxes->isNotEmpty())
                                                        @foreach ($taxes as $tax)
                                                            <option value="{{ $tax->id }}"
                                                                {{ old('tax_id') == $tax->id ? 'selected' : '' }}>
                                                                {{ $tax->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No tax available</option>
                                                    @endif
                                                </select>
                                                <div class="invalid-feedback" id="role_idError">
                                                    @error('tax_id')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                </div>


                                <!-- Custom Fields Tab -->
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-create :customFields="$customFields" />
                                @endif

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
            const tabs = document.querySelectorAll('#companyTabs .nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // You can add custom logic here if needed
                    console.log(`Switched to tab: ${this.textContent}`);
                });
            });





        });
    </script>
@endpush
