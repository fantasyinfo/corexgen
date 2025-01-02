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
                    <x-form-components.input-label for="nameName" class="custom-class" required>
                        {{ __('products.Product Type') }}
                    </x-form-components.input-label>
                    <select name="type" id="type" class="form-select"  data-filter="type">
                        <option value="Product" {{ request('type') == 'Product' ? 'selected' : '' }}
                            {{ $type == 'Product' ? 'selected' : '' }}>Product
                        </option>
                        <option value="Service" {{ request('type') == 'Service' ? 'selected' : '' }}
                            {{ $type == 'Service' ? 'selected' : '' }}>Services</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-group">
                    <x-form-components.input-label for="nameFilter" class="custom-class" >
                        {{ __('products.Title') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="nameFilter" name="title"
                        placeholder="{{ __('Mackbook Pro M2') }}" value="{{ request('title') }}" data-filter="title" />
                </div>
            </div>
 
       
       
            <div class="mb-3">
                <div class="form-group">
                    <label for="clientFilter" class="mb-2 ">{{ __('products.Select Category') }}</label>
                    <select name="cgt_id" class="form-select searchSelectBox" id="clientFilter" data-filter="cgt_id">
                        <option value="">{{ __('products.Category') }}</option>
                        @foreach ($categories as $item)
                            <option value="{{$item->id}}" {{ request('cgt_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
       
            <div class="mb-3">
                <div class="form-group">
                    <label for="statusFilter" class="mb-2 ">{{ __('products.All Statuses') }}</label>
                    <select name="status" class="form-select" id="statusFilter" data-filter="status">
                        <option value="">{{ __('products.All Statuses') }}</option>
                        @foreach (CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS'] as $item)
                            <option value="{{$item}}" {{ request('status') == $item ? 'selected' : '' }}>{{$item}}</option>
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
