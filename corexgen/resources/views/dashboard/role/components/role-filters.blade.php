@if (hasPermission('ROLE.FILTER'))
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
                    <x-form-components.input-label for="roleDesc" class="custom-class">
                        {{ __('crm_role.Role Name') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" name="name" id="nameFilter" data-filter="name"
                        placeholder="{{ __('Admin/Manager') }}" value="{{ old('name') }}" class="custom-class" />

                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="roleDesc" class="custom-class">
                        {{ __('crm_role.All Statuses') }}
                    </x-form-components.input-label>
                    <select name="status" class="form-select" id="statusFilter" data-filter="status">
                        <option value="">{{ __('crm_role.All Statuses') }}</option>
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
