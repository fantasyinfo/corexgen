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
                    <x-form-components.input-label for="nameFilter" class="custom-class">
                        {{ __('clients.Client Name') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" name="cname" id="nameFilter"
                        placeholder="{{ __('Enter Client Name') }}" value="{{ request('name') }}" required
                        class="custom-class" />


                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="emailFilter" class="custom-class">
                        {{ __('clients.Email') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="email" name="email" id="emailFilter"
                        placeholder="{{ __('Enter Client Email') }}" value="{{ request('email') }}" required
                        class="custom-class" />


                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="phoneFilter" class="custom-class">
                        {{ __('clients.Phone') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="tel" name="email" id="phoneFilter"
                        placeholder="{{ __('Enter Client Phone') }}" value="{{ request('phone') }}" required
                        class="custom-class" />


                </div>
            </div>

            <!-- Date Filters -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="startDateFilter" class="custom-class">
                        {{ __('clients.Start Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" name="start_date" id="startDateFilter"
                        value="{{ request('start_date') }}" class="custom-class" />
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="endDateFilter" class="custom-class">
                        {{ __('clients.End Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" name="end_date" id="endDateFilter"
                        value="{{ request('end_date') }}" class="custom-class" />
                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="statusFilter" class="custom-class">
                        {{ __('clients.All Statuses') }}
                    </x-form-components.input-label>

                    <select name="status" class="form-select" id="statusFilter">
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
