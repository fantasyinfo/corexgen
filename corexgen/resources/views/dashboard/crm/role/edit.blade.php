@extends('layout.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
               

                <form id="roleForm" action="{{ route('crm.role.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type='hidden' name='id' value='{{$role["id"]}}' />
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ __('crm_role.Update Role') }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                            </h5>
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-plus me-2"></i> <span>{{ __('crm_role.Update Role') }}</span>
                            </button>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-4">
                                <label for="roleName" class="fw-semibold">{{ __('crm_role.Role Name') }}: <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-lg-8">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="feather-user"></i></div>
                                    <input type="text" 
                                           class="form-control @error('role_name') is-invalid @enderror" 
                                           id="roleName" 
                                           name="role_name" 
                                           placeholder="{{ __('Admin/Manager') }}"
                                           value="{{ $role['role_name']}}"
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
                                    <div class="input-group-text"><i class="feather-file-text"></i></div>
                                    <textarea 
                                        class="form-control @error('role_desc') is-invalid @enderror" 
                                        id="roleDesc" 
                                        name="role_desc" 
                                        placeholder="{{ __('Description for the role') }}"
                                        rows="3">{{ $role['role_desc'] }}</textarea>
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
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('roleForm');
            const roleName = document.getElementById('roleName');
            const roleDesc = document.getElementById('roleDesc');
            const roleNameError = document.getElementById('roleNameError');
            const roleDescError = document.getElementById('roleDescError');
        
            // Real-time validation for role name
            roleName.addEventListener('blur', function() {
                validateField('role_name', this.value);
            });
        
            // Real-time validation for role description
            roleDesc.addEventListener('blur', function() {
                validateField('role_desc', this.value);
            });
        
            // Draft button functionality
            document.getElementById('draftButton').addEventListener('click', function() {
                // Implement draft saving logic here
                alert('Draft saving functionality to be implemented');
            });
        
            function validateField(field, value) {
                fetch('{{ route("crm.role.validate-field") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ field, value })
                })
                .then(response => response.json())
                .then(data => {
                    const errorElement = document.getElementById(`${field}Error`);
                    const inputElement = document.getElementById(field === 'role_name' ? 'roleName' : 'roleDesc');
        
                    if (!data.valid) {
                        inputElement.classList.add('is-invalid');
                        errorElement.textContent = data.errors[0] || 'Invalid input';
                    } else {
                        inputElement.classList.remove('is-invalid');
                        errorElement.textContent = '';
                    }
                });
            }
        });
        </script>

        @endpush