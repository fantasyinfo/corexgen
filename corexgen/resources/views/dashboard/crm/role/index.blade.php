@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">{{ __('Roles Management') }}</h5>
            <div class="card-header-action">
                <a href="{{ route('crm.role.create') }}" class="btn btn-primary me-2">
                    <i class="feather feather-plus"></i> {{ __('Create Role') }}
                </a>
                <a href="{{ route('crm.role.export', request()->all()) }}" class="btn btn-outline-secondary">
                    <i class="feather feather-download"></i> {{ __('Export') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Advanced Filter Form -->
            <form action="{{ route('crm.role.index') }}" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" 
                               placeholder="{{ __('Role Name') }}" 
                               value="{{ request('name') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                {{ __('Active') }}
                            </option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                {{ __('Inactive') }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}"
                               placeholder="{{ __('Start Date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}"
                               placeholder="{{ __('End Date') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="per_page" class="form-select">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather feather-search"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Roles Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort' => 'role_name', 
                                    'direction' => request('sort') == 'role_name' && request('direction') == 'asc' ? 'desc' : 'asc'
                                ]) }}">
                                    {{ __('Role Name') }}
                                    @if(request('sort') == 'role_name')
                                        {!! request('direction') == 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->role_name }}</td>
                                <td>{{ Str::limit($role->role_desc, 50) }}</td>
                                <td>
                                    <span class="badge {{ $role->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($role->status) }}
                                    </span>
                                </td>
                                <td>{{ $role->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                 

                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md ms-auto" data-bs-toggle="dropdown" data-bs-offset="0,28" aria-expanded="false">
                                            <i class="feather feather-more-horizontal"></i>
                                        </a>
                                        <ul class="dropdown-menu" style="">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('crm.role.edit', $role) }}">
                                                    <i class="feather feather-edit-3 me-3"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                            </li>
                                            
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('crm.role.destroy', $role) }}" method="POST" 
                                                onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                              @csrf
                                              @method('DELETE')
                                              <button type="submit" class="dropdown-item text-danger">
                                                  <i class="feather feather-trash-2 me-2"></i>{{ __('Delete') }}
                                              </button>
                                          </form>
                                               
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ __('No roles found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ __('Showing') }} {{ $roles->firstItem() }} - {{ $roles->lastItem() }} 
                    {{ __('of') }} {{ $roles->total() }} {{ __('results') }}
                </div>
                {{ $roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection