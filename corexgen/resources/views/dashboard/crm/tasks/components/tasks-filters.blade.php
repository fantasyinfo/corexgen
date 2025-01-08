@if (hasPermission('TASKS.FILTER'))
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
                    <x-form-components.input-label for="titleFileter" class="custom-class">
                        {{ __('tasks.Title') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" name="title" data-filter="title" id="titleFileter"
                        placeholder="{{ __('Enter Title') }}" value="{{ request('title') }}" required
                        class="custom-class" />


                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="relatedTo" class="custom-class">
                        {{ __('tasks.Related To') }}
                    </x-form-components.input-label>

                    <select class="form-select" name="related_to" id="related_to"  data-filter="related_to">
                        <option selected value="0">Select Related To</option>
                        @foreach (TASKS_RELATED_TO['STATUS'] as $key => $pri)
                            <option value="{{ $key }}"
                                {{ request('related_to') == $key ? 'selected' : '' }}>
                                {{ $pri }}</option>
                        @endforeach
                    </select>


                </div>
            </div>
        
            <!-- Date Filters -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="startDateFilter" class="custom-class">
                        {{ __('tasks.Start Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" placeholder="Select Date" data-filter="start_date" name="start_date"
                        id="startDateFilter" value="{{ request('start_date') }}" class="custom-class" />
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="endDateFilter" class="custom-class">
                        {{ __('tasks.Due Date') }}
                    </x-form-components.input-label>
                    <x-form-components.input-group type="date" placeholder="Select Date" data-filter="due_date" name="due_date"
                        id="endDateFilter" value="{{ request('due_date') }}" class="custom-class" />
                </div>
            </div>

            <!-- stage Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="stageFilter" class="custom-class">
                        {{ __('tasks.Stage') }}
                    </x-form-components.input-label>

                    <select name="status_id" class="form-select" id="stageFilter" data-filter="status_id">
                        <option selected value="0">Select Stages</option>
                        @foreach ($tasksStatus as $ls)
                            <option value="{{ $ls->id }}"
                                {{ request('status_id') == $ls->id ? 'selected' : '' }}>
                                {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="stageFilter" class="custom-class">
                        {{ __('tasks.Project') }}
                    </x-form-components.input-label>

                    <select class="form-select searchSelectBox" name="project_id"
                    id="project_id" data-filter="project_id">
                    <option selected value="0">Select Project</option>
                    @foreach ($projects as $pro)
                        <option value="{{ $pro->id }}"
                            {{ old('project_id') == $pro->id ? 'selected' : '' }}>
                            {{ $pro->title }}</option>
                    @endforeach
                </select>
                </div>
            </div>

            <!-- groups Dropdown -->
       
            <!-- assign_to Dropdown -->
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="assignToFilter" class="custom-class">
                        {{ __('tasks.Assigns To') }}
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
                        {{ __('tasks.Assign By') }}
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
