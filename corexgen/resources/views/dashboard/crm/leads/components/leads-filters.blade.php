@if (hasPermission('LEADS.FILTER'))
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
                        {{ __('leads.Name') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" name="name" data-filter="name" id="nameFilter"
                        placeholder="{{ __('Enter Name') }}" value="{{ request('name') }}" required
                        class="custom-class" />


                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="emailFilter" class="custom-class">
                        {{ __('leads.Email') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="email" name="email" data-filter="email" id="emailFilter"
                        placeholder="{{ __('Enter Email') }}" value="{{ request('email') }}" required
                        class="custom-class" />


                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="phoneFilter" class="custom-class">
                        {{ __('leads.Phone') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="tel" name="phone" data-filter="phone" id="phoneFilter"
                        placeholder="{{ __('Enter Phone') }}" value="{{ request('phone') }}" required
                        class="custom-class" />


                </div>
            </div>

            <!-- Date Filters -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="startDateFilter" class="custom-class">
                        {{ __('leads.Start Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" data-filter="start_date" name="start_date"
                        id="startDateFilter" value="{{ request('start_date') }}" class="custom-class" />
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="endDateFilter" class="custom-class">
                        {{ __('leads.End Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" data-filter="end_date" name="end_date"
                        id="endDateFilter" value="{{ request('end_date') }}" class="custom-class" />
                </div>
            </div>

            <!-- stage Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="stageFilter" class="custom-class">
                        {{ __('leads.Stage') }}
                    </x-form-components.input-label>

                    <select name="status_id" class="form-select" id="stageFilter" data-filter="status_id">
                        <option selected value="0">Select Stages</option>
                        @foreach ($leadsStatus as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('status_id') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- groups Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="groupFilter" class="custom-class">
                        {{ __('leads.Group') }}
                    </x-form-components.input-label>

                    <select name="group_id" class="form-select" id="groupFilter" data-filter="group_id">
                        <option selected value="0">Select Group</option>
                        @foreach ($leadsGroups as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('group_id') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- sources Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="sourceFilter" class="custom-class">
                        {{ __('leads.Source') }}
                    </x-form-components.input-label>

                    <select name="status" class="form-select" id="sourceFilter" data-filter="source_id">
                        <option selected value="0">Select Sources</option>
                        @foreach ($leadsSources as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('source_id') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- assign_to Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="assignToFilter" class="custom-class">
                        {{ __('leads.Assigns To') }}
                    </x-form-components.input-label>

                    <select name="assign_to[]" class="form-select searchSelectBox" id="assignToFilter" multiple="multiple" data-filter="assign_to">
                        @foreach ($teamMates as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('assign_to') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <!-- assign_by Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="assignByFilter" class="custom-class">
                        {{ __('leads.Assign By') }}
                    </x-form-components.input-label>

                    <select name="status" class="form-selec searchSelectBox" id="assignByFilter" data-filter="assign_by">
                        <option selected value="0">Select Assign By</option>
                        @foreach ($teamMates as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('assign_by') == $ls->id ? 'selected' : '' }}>
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
