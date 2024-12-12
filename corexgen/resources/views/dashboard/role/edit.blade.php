@extends('layout.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="justify-content-md-center col-lg-9">
                <div class="card stretch stretch-full">


                    <form id="roleForm" action="{{ route(getPanelRoutes('role.update')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type='hidden' name='id' value='{{ $role['id'] }}' />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block mb-2">{{ __('crm_role.Update Role') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('crm_role.Update Role') }}</span>
                                </button>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="roleName" class="custom-class" required>
                                        {{ __('crm_role.Role Name') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="roleName"
                                        name="role_name" placeholder="{{ __('Admin/Manager') }}"
                                        value="{{ $role['role_name'] }}" required />
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="roleDesc" class="custom-class" >
                                        {{ __('crm_role.Description') }}
                                    </x-form-components.input-label>
                                 
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.textarea-group name="role_desc" id="roleDesc"
                                    placeholder="Describe the role" value="{{ $role['role_desc'] }}" class="custom-class"
                                     />
                                  
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
