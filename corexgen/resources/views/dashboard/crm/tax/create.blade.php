@extends('layout.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-lg-9">
            <div class="card stretch stretch-full">
           

                <form id="userForm" action="{{ route(getPanelRoutes('tax.store')) }}" method="POST">
                    @csrf
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <p class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('tax.Create New Tax') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i>  <span>{{ __('tax.Create Tax') }}</span>
                            </button>
                        </div>
                
        
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="nameName" class="fw-semibold">{{ __('tax.Tax Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                  
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="nameName" 
                                           name="name" 
                                           placeholder="{{ __('India GST Tax') }}"
                                           value="{{ old('name') }}"
                                           required>
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
                                <label for="country_id" class="fw-semibold">{{ __('tax.Select Country') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                  
                                    <select class="form-control searchSelectBox select2-hidden-accessible @error('country_id') is-invalid @enderror" name="country_id" id="country_id">
                                        @if($countries && $countries->isNotEmpty())
                                            @foreach($countries as $countries)
                                                <option value="{{ $countries->id }}" {{ old('country_id') == $countries->id ? 'selected' : '' }}>
                                                    {{ $countries->name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option disabled>No contries available</option>
                                        @endif
                                    </select>
                                    <div class="invalid-feedback" id="country_idError">
                                        @error('country_id')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="tax_typeName" class="fw-semibold">{{ __('tax.Tax Type') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                   
                                    <input type="text" 
                                           class="form-control @error('tax_type') is-invalid @enderror" 
                                           id="tax_typeName" 
                                           name="tax_type" 
                                           required
                                           placeholder="{{ __('GST / VAT') }}"
                                           value="{{ old('tax_type') }}">
                                    <div class="invalid-feedback" id="tax_typeNameError">
                                        @error('tax_type')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                
                  
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="tax_rate" class="fw-semibold">{{ __('tax.Tax Rate') }} (%): <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                   
                                    <input 
                                    type="number"
                                    min="0" 
                                    max="100" step="0.01"
                                           class="form-control @error('tax_rate') is-invalid @enderror" 
                                           id="tax_rateName" 
                                           name="tax_rate" 
                                           required
                                           placeholder="{{ __('18') }}"
                                           value="{{ old('tax_rate') }}">
                                    <div class="invalid-feedback" id="tax_rateNameError">
                                        @error('tax_rate')
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
