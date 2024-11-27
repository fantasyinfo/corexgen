@if (hasPermission('USERS.FILTER'))
    <div id="filter-section">

        <div class="card-title">
            {{ __('crud.Filter') }}

        </div>
        <!-- Advanced Filter Form -->

        <div class="row g-3">
            <!-- Search Input -->
            <div class="col-md-3">
                <div class="form-group">
                    <label for="nameFilter" class="mb-2 font-12">{{ __('users.Name') }}</label>
                    <input type="text" id="nameFilter" name="name" class="form-control"
                        placeholder="{{ __('users.Name') }}" value="{{ request('name') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="emailFilter" class="mb-2 font-12">{{ __('users.Email') }}</label>
                    <input type="text" id="emailFilter" name="email" class="form-control"
                        placeholder="{{ __('users.Email') }}" value="{{ request('email') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="roleFilter" class="mb-2 font-12">{{ __('users.Role') }}</label>
                    <select id="roleFilter"
                        class="form-control searchSelectBox select2-hidden-accessible @error('role_id') is-invalid @enderror"
                        name="role_id" id="role_id">
                        <option selected value="0" >Select Role</option>
                        @if ($roles && $roles->isNotEmpty())
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->role_name }}
                                </option>
                            @endforeach
                        @else
                            <option disabled>No roles available</option>
                        @endif
                    </select>
                </div>
            </div>


            <!-- Status Dropdown -->
            <div class="col-md-3">
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
