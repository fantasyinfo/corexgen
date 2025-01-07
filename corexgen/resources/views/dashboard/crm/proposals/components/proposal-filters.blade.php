@if (hasPermission('PROPOSALS.FILTER'))
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
                    <x-form-components.input-label for="nameFilter" class="custom-class" >
                        {{ __('proposals.Title') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="nameFilter" name="title"
                        placeholder="{{ __('John Doe') }}" value="{{ request('title') }}" data-filter="title" />
                </div>
            </div>
 
       
       
            <div class="mb-3">
                <div class="form-group">
                    <label for="clientFilter" class="mb-2 ">{{ __('proposals.Select Client') }}</label>
                    <select name="typable_id" class="form-select searchSelectBox" id="clientFilter" data-filter="client_id">
                        <option value="">{{ __('proposals.Client') }}</option>
                        @foreach ($clients as $item)
                            <option value="{{$item->id}}" {{ request('typable_id') == $item->id ? 'selected' : '' }}>{{ $item->first_name . ' ' . $item->last_name . ' [' . $item->primary_email . ']' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="leadFilter" class="mb-2 ">{{ __('proposals.Select Lead') }}</label>
                    <select name="typable_id" class="form-select searchSelectBox" id="leadFilter" data-filter="lead_id">
                        <option value="">{{ __('proposals.Lead') }}</option>
                        @foreach ($leads as $item)
                            <option value="{{$item->id}}" {{ request('typable_id') == $item->id ? 'selected' : '' }}>{{ $item->first_name . ' ' . $item->last_name . ' [' . $item->email . ']' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="statusFilter" class="mb-2 ">{{ __('proposals.All Statuses') }}</label>
                    <select name="status" class="form-select" id="statusFilter" data-filter="status">
                        <option value="">{{ __('proposals.All Statuses') }}</option>
                        @foreach (CRM_STATUS_TYPES['PROPOSALS']['STATUS'] as $item)
                            <option value="{{$item}}" {{ request('status') == $item ? 'selected' : '' }}>{{$item}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-group">
                    <label for="dateFilter" class="mb-2 ">{{ __('proposals.Date') }}</label>
                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="dateFilter" name="creating_date"
                    value="{{ request('creating_date') }}" data-filter="creating_date" />
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <label for="vdateFilter" class="mb-2 ">{{ __('proposals.Valid Till') }}</label>
                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="vdateFilter" name="valid_date"
                    value="{{ request('valid_date') }}" data-filter="valid_date" />
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
