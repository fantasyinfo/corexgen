@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="justify-content-md-center col-lg-9">
            <div class="card stretch stretch-full">
           

                <form id="roleForm" action="{{ route(getPanelRoutes('role.store')) }}" method="POST">
                    @csrf
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <p class="fw-bold mb-0 me-4">
                                <span class="d-block">{{ __('crm_role.Create New Role') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span>{{ __('crm_role.Create Role') }}</span>
                            </button>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="roleName" class="fw-semibold">{{ __('crm_role.Role Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                           
                                    <input type="text" 
                                           class="form-control @error('role_name') is-invalid @enderror" 
                                           id="roleName" 
                                           name="role_name" 
                                           placeholder="{{ __('Admin/Manager') }}"
                                           value="{{ old('role_name') }}"
                                           required>
                                    <div class="invalid-feedback" id="roleNameError">
                                        @error('role_name')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="roleDesc" class="fw-semibold">{{ __('crm_role.Description') }}:</label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                  
                                    <textarea 
                                        class="form-control @error('role_desc') is-invalid @enderror" 
                                        id="roleDesc" 
                                        name="role_desc" 
                                        placeholder="{{ __('Description for the role') }}"
                                        rows="3">{{ old('role_desc') }}</textarea>
                                    <div class="invalid-feedback" id="roleDescError">
                                        @error('role_desc')
                                            {{ $message }}
                                        @enderror
                                    </div>
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
 
       