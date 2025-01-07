@if (hasPermission('PROJECTS.FILTER'))
    <div class="filter-sidebar" id="filterSidebar">

        <div class="filter-sidebar-content">


            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Advanced {{ __('crud.Filter') }}</h5>

                <button type="button" class="btn btn-light" id="closeFilter" aria-label="Close">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
            <!-- Advanced Filter Form -->

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="typeFilter" class="custom-class">
                        {{ __('projects.Billing Type') }}
                    </x-form-components.input-label>

                    <select name="type" class="form-select" id="typeFilter" data-filter="billing_type">
                        <option selected value="0">Select Billing Type</option>
                        <option value="Hourly" {{ request('billing_type') === 'Hourly' ? 'selected' : '' }}>Hourly
                        </option>
                        <option value="One-Time" {{ request('billing_type') === 'One-Time' ? 'selected' : '' }}>One-Time
                        </option>

                    </select>
                </div>
            </div>

            <!-- Search Input -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="title" class="custom-class">
                        {{ __('projects.Title') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" name="title" data-filter="title" id="title"
                        placeholder="{{ __('Enter Title') }}" value="{{ request('title') }}" required
                        class="custom-class" />


                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="client_id" class="custom-class">
                        {{ __('projects.Client') }}
                    </x-form-components.input-label>

                    <select name="client_id" id="client_id" class="form-select searchSelectBox" data-filter="client_id">
                        <option selected value="0">Select Client</option>
                        @foreach ($clients as $item)
                            @php
                                $nameAndEmail = $item->first_name . ' ' . $item->last_name;
                                if ($item->type == 'Company') {
                                    $nameAndEmail = $item->company_name;
                                }
                                $nameAndEmail .= !$item->primary_email
                                    ? ' [No Email Found...] '
                                    : " [ $item->primary_email ]";
                            @endphp
                            <option value="{{ $item->id }}" {{ old('client_id') == $item->id ? 'selected' : '' }}>
                                {{ $nameAndEmail }}
                            </option>
                        @endforeach
                    </select>

                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="startDate" class="custom-class">
                        {{ __('projects.Start Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" placeholder="Select Date" data-filter="start_date" name="start_date" id="startDate"
                        value="{{ request('start_date') }}" class="custom-class" />
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="dueDate" class="custom-class">
                        {{ __('projects.Due Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" placeholder="Select Date" data-filter="due_date" name="due_date" id="dueDate"
                        value="{{ request('due_date') }}" class="custom-class" />
                </div>
            </div>

            <!-- Status Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="statusFilter" class="custom-class">
                        {{ __('projects.All Statuses') }}
                    </x-form-components.input-label>

                    <select name="status" class="form-select" id="statusFilter" data-filter="status">
                        <option selected value="0">Select Status</option>
                        @foreach (CRM_STATUS_TYPES['PROJECTS']['STATUS'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- assign_to Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="assignToFilter" class="custom-class">
                        {{ __('projects.Assigns To') }}
                    </x-form-components.input-label>

                    <select name="assign_to[]" class="form-select searchSelectBox" id="assignToFilter"
                        multiple="multiple" data-filter="assign_to">
                        @foreach ($teamMates as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('assign_to') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
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
