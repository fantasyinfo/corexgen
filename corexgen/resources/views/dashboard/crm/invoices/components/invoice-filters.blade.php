@if (hasPermission('INVOICES.FILTER'))
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
                    <label for="clientFilter" class="mb-2 ">{{ __('invoices.Select Client') }}</label>
                    <select name="client_id" class="form-select searchSelectBox" id="clientFilter" data-filter="client_id">
                        <option value="">{{ __('invoices.Client') }}</option>
                        @foreach ($clients as $item)
                            <option value="{{$item->id}}" {{ request('client_id') == $item->id ? 'selected' : '' }}>{{ $item->first_name . ' ' . $item->last_name . ' [' . $item->primary_email . ']' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="clientFilter" class="mb-2 ">{{ __('invoices.Select Task') }}</label>
                    <select name="task_id" id="task_id" class="form-select searchSelectBox" data-filter="task_id">
                        <option value="">{{ __('invoices.Select Task') }}</option>
                        @foreach ($tasks as $item)
                            <option value="{{ $item->id }}"
                                {{ request('task_id') == $item->id ? 'selected' : '' }}
                               >{{ $item->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
  
            <div class="mb-3">
                <div class="form-group">
                    <label for="statusFilter" class="mb-2 ">{{ __('invoices.All Statuses') }}</label>
                    <select name="status" class="form-select" id="statusFilter" data-filter="status">
                        <option value="">{{ __('invoices.All Statuses') }}</option>
                        @foreach (CRM_STATUS_TYPES['INVOICES']['STATUS'] as $item)
                            <option value="{{$item}}" {{ request('status') == $item ? 'selected' : '' }}>{{$item}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <label for="dateFilter" class="mb-2 ">{{ __('invoices.Date') }}</label>
                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="dateFilter" name="issue_date"
                    value="{{ request('issue_date') }}" data-filter="issue_date" />
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="vdateFilter" class="mb-2 ">{{ __('invoices.Due Date') }}</label>
                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="vdateFilter" name="due_date"
                    value="{{ request('due_date') }}" data-filter="due_date" />
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
