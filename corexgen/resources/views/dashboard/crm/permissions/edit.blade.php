@extends('layout.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <form id="roleForm" action="{{ route('crm.permissions.store') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('crm_permissions.Update Permissions') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </h5>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-plus me-2"></i> <span>{{ __('crm_permissions.Update Permissions') }}</span>
                            </button>
                        </div>

                          <!-- Role Selection Field -->
                          <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="role_id" class="fw-semibold">{{ __('crm_permissions.Select Role') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fa-regular fa-user"></i></div>
                                    <select class="form-control select2-hidden-accessible @error('role_id') is-invalid @enderror" 
                                    name="role_id" 
                                    id="role_id">
                                @if($roles && $roles->isNotEmpty())
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" 
                                                {{ $role->id == $role->id ? 'selected' : '' }}>
                                            {{ $role->role_name }}
                                        </option>
                                    @endforeach
                                @else
                                    <option disabled>No roles available</option>
                                @endif
                            </select>
                                    <div class="invalid-feedback" id="role_idError">
                                        @error('role_id')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- Role Selection Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-12">
                                    <div class="row">

                                    
                                    @foreach($crm_permissions->where('parent_menu', '1') as $parentMenu)
                                    <div class="col-md-4 mt-5">

                                   
                                    <div class="form-check mb-2">
                                        <input 
                                        type='checkbox' 
                                        class='form-check-input parent-checkbox' 
                                        id='parent_{{$parentMenu->permission_id}}' 
                                        name='permissions[]'
                                        value='{{$parentMenu->permission_id}}'
                                        {{ in_array($parentMenu->permission_id, $existingPermissions) ? 'checked' : '' }}
                                    />
                                        <label class="form-check-label" for='parent_{{$parentMenu->id}}'>
                                            {{$parentMenu->name}}
                                        </label>
                                        
                                        @if($crm_permissions->where('parent_menu_id', $parentMenu->id)->count())
                                            <ul class="nxl-submenu mt-3">
                                                @foreach($crm_permissions->where('parent_menu_id', $parentMenu->id) as $childMenu)
                                                    <li class="nxl-item form-check">
                                                        <input 
                                                        type='checkbox' 
                                                        class='form-check-input child-checkbox child_{{$parentMenu->permission_id}}' 
                                                        id='child_{{$childMenu->permission_id}}'
                                                        name='permissions[]'
                                                        value='{{$childMenu->permission_id}}'
                                                        {{ in_array($childMenu->permission_id, $existingPermissions) ? 'checked' : '' }}
                                                    />
                                                        <label class="form-check-label" for='child_{{$childMenu->id}}'>
                                                            {{$childMenu->name}}
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                                </div>
                            </div>
                     
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    @endsection

        
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Parent checkbox click handler
            const parentCheckboxes = document.querySelectorAll('.parent-checkbox');
            parentCheckboxes.forEach(parentCheckbox => {
                parentCheckbox.addEventListener('change', function() {
                    const parentId = this.id.replace('parent_', '');
                    const childCheckboxes = document.querySelectorAll(`.child_${parentId}`);
                    
                    childCheckboxes.forEach(childCheckbox => {
                        childCheckbox.checked = this.checked;
                    });
                });
            });
        
            // Child checkbox click handler
            const childCheckboxes = document.querySelectorAll('.child-checkbox');
            childCheckboxes.forEach(childCheckbox => {
                childCheckbox.addEventListener('change', function() {
                    const parentId = this.classList[2].replace('child_', '');
                    const parentCheckbox = document.getElementById(`parent_${parentId}`);
                    const allChildCheckboxes = document.querySelectorAll(`.child_${parentId}`);
                    
                    // Check if all child checkboxes are checked
                    const allChildChecked = Array.from(allChildCheckboxes).every(cb => cb.checked);
                    parentCheckbox.checked = allChildChecked;
                });
            });
        });
        </script>
    @endpush