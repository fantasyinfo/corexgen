@if (hasPermission('TAX.FILTER'))
    <div class="filter-sidebar" id="filterSidebar">
        <div class="filter-sidebar-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Advanced {{ __('crud.Filter') }}</h5>

                <button type="button" class="btn btn-light" id="closeFilter" aria-label="Close">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>

            <!-- Search Input -->
            <div class="mb-3">
                <div class="form-group">
                    <label class="mb-2 font-12">Tax Name</label>
                    <input type="text" id="nameFilter" name="name" class="form-control"
                        placeholder="" value="{{ request('name') }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label class="mb-2 font-12">Select Contry</label>
                    <select class="form-control searchSelectBox select2-hidden-accessible @error('country_id') is-invalid @enderror" name="country_id" id="countryFilter">
                        <option selected value="0">Select Country</option>
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
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <label class="mb-2 font-12">Tax Rate</label>
                    <input   type="number"
                                    min="0" 
                                    max="100" step="0.01" id="taxRateFilter" name="tax_rate" class="form-control"
                        placeholder="" value="{{ request('tax_rate') }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label class="mb-2 font-12">Tax Type</label>
                    <input type="text" id="taxTypeFilter" name="tax_type" class="form-control"
                        placeholder="" value="{{ request('tax_type') }}">
                </div>
            </div>
     
        

            <!-- Status Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <label class="mb-2 font-12">Status</label>
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">{{ __('tax.All Statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            {{ __('Active') }}
                        </option>
                        <option value="deactive" {{ request('status') == 'deactive' ? 'selected' : '' }}>
                            {{ __('Inactive') }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary w-100" id="filterBtn">
                    <i class="fas fa-search me-2"></i>Apply Filters
                </button>
                <button type="button" class="btn btn-light w-100" id="clearFilter">
                    <i class="fas fa-trash-alt me-2"></i>Clear
                </button>
            </div>


        </div>
    </div>



    </div>
@endif
