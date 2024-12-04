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
                    <label for="nameFilter" class="mb-2 font-12">{{ __('users.Name') }}</label>
                    <input type="text" id="nameFilter" name="name" class="form-control"
                        placeholder="{{ __('users.Name') }}" value="{{ request('name') }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="emailFilter" class="mb-2 font-12">{{ __('users.Email') }}</label>
                    <input type="text" id="emailFilter" name="email" class="form-control"
                        placeholder="{{ __('users.Email') }}" value="{{ request('email') }}">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="roleFilter" class="mb-2 font-12">{{ __('company.Plans') }}</label>
                    <select id="roleFilter"
                        class="form-control searchSelectBox  @error('plans') is-invalid @enderror"
                        name="plans" id="plans">
                        <option selected value="0">Select Plans</option>
                        @if ($plans && $plans->isNotEmpty())
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ request('plans') == $plan->id ? 'selected' : '' }}>
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
                    <label for="statusFilter" class="mb-2 font-12">{{ __('users.All Statuses') }}</label>
                    <select name="status" class="form-select" id="statusFilter">
                        <option value="">{{ __('users.All Statuses') }}</option>
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
