@if (hasPermission('ROLE.FILTER'))
    <div id="filter-section">

        <div class="card-title">
            {{ __('crud.Filter') }}

        </div>
        <!-- Advanced Filter Form -->

        <div class="row g-3">
            <!-- Search Input -->
            <div class="col-md-4">
                <div class="form-group">
                    <input type="text" id="nameFilter" name="name" class="form-control"
                        placeholder="{{ __('crm_role.Role Name') }}" value="{{ request('name') }}">
                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="col-md-4">
                <div class="form-group">
                    <select name="status" class="form-select" id="statusFilter">
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
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="button" id="filterBtn" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>{{ __('Search') }}
                    </button>
                    <button type="button" id="clearFilter" class="btn btn-light">
                        <i class="fas fa-trash-alt me-1"></i>{{ __('Clear') }}
                    </button>
                </div>
            </div>
        </div>

    </div>
@endif
