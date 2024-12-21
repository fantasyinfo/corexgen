@if (hasPermission('USERS.FILTER'))
    <div class="filter-sidebar" id="filterSidebar">

        <div class="filter-sidebar-content">


            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Advanced {{ __('crud.Filter') }}</h5>
             
                <button type="button" class="btn btn-light" id="closeFilter" aria-label="Close">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
            <!-- Advanced Filter Form -->


            <!-- Search Input -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="nameFilter" class="custom-class" required>
                        {{ __('companies.Company Name') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" data-filter="name" name="name" id="nameFilter"
                    placeholder="{{ __('Enter Company Name') }}" value="{{ request('name') }}"
                    required class="custom-class" />
                  
                 
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="emailFilter" class="custom-class" required>
                        {{ __('companies.Email') }}
                    </x-form-components.input-label>
              
                    <x-form-components.input-group type="email" data-filter="email"  name="email" id="emailFilter"
                    placeholder="{{ __('Enter Company Email') }}" value="{{ request('email') }}"
                    required class="custom-class" />

        
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="plansFilter" class="custom-class" required>
                        {{ __('companies.Plans') }}
                    </x-form-components.input-label>
                  
                    <select id="plansFilter"
                        class="form-control searchSelectBox  @error('plans') is-invalid @enderror"
                        name="plans" id="plans" data-filter="plans" >
                        <option selected value="0">Select Plans</option>
                        @if ($plans && $plans->isNotEmpty())
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->name }}"
                                    {{ request('plans') == $plan->name ? 'selected' : '' }}>
                                    {{ $plan->name }}
                                </option>
                            @endforeach
                        @else
                            <option disabled>No plans available</option>
                        @endif
                    </select>
                </div>
            </div>
            <!-- Status Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="statusFilter" class="custom-class" required>
                        {{ __('companies.All Statuses') }}
                    </x-form-components.input-label>
                 
                    <select name="status" class="form-select" id="statusFilter" data-filter="status" >
                        <option selected value="0">Select Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            {{ __('Active') }}
                        </option>
                        <option value="deactive" {{ request('status') == 'deactive' ? 'selected' : '' }}>
                            {{ __('Inactive') }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Filter Action Buttons -->
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
@endif
