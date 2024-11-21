@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">{{ __('crm_permissions.Permissions Management') }}</h5>
            <div class="card-header-action">
                @if(hasPermission('PERMISSIONS.CREATE'))
                <a href="{{ route('crm.permissions.create') }}" class="btn btn-md btn-primary me-2">
                    <i class="feather feather-plus"></i> <span>{{ __('crm_permissions.Create Permissions') }}</span>
                </a>
                @endif
        
            </div>
        </div>

        <div class="card-body">
            <!-- Roles Table -->
            <div class="table-responsive">
                @if(hasPermission('PERMISSIONS.READ_ALL') || hasPermission('PERMISSIONS.READ') )
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort' => 'role_name', 
                                    'direction' => request('sort') == 'role_name' && request('direction') == 'asc' ? 'desc' : 'asc'
                                ]) }}">
                                    {{ __('crm_permissions.Role Name') }}
                                    @if(request('sort') == 'role_name')
                                        {!! request('direction') == 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
              
                           
                         
                   
                            <th class="text-end">{{ __('crud.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $role)
                            <tr>
                                <td>{{ $role->role_name }}</td>
                                @if(hasPermission('PERMISSIONS.UPDATE') || hasPermission('PERMISSIONS.DELETE'))
                                <td class="text-end">
                                 

                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md ms-auto" data-bs-toggle="dropdown" data-bs-offset="0,28" aria-expanded="false">
                                            <i class="feather feather-more-horizontal"></i>
                                        </a>
                                        <ul class="dropdown-menu" style="">
                                            @if(hasPermission('PERMISSIONS.UPDATE'))
                                            <li>
                                                <a class="dropdown-item" href="{{ route('crm.permissions.edit',['id' => $role->id] ) }}">
                                                    <i class="feather feather-edit-3 me-3"></i>
                                                    <span>{{ __('crud.Edit') }}</span>
                                                </a>
                                            </li>
                                            @endif
                                            @if(hasPermission('PERMISSIONS.DELETE'))
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('crm.permissions.destroy', ['id' => $role->id]) }}" method="POST" 
                                                onsubmit="return confirm('{{ __('crud.Are you sure?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="feather feather-trash-2 me-2"></i>{{ __('crud.Delete') }}
                                                    </button>
                                                </form>
                                               
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                                @else
                                <td class="text-end">...</td>
                                @endif
                               
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center"><i class="fa-solid fa-file"></i> {{ __('crm_permissions.No Permissions Found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @else 
                <table class="table table-hover">
                    <tbody>
                            <tr>
                                <td colspan="5" class="text-center"><i class="fa-solid fa-file"></i> {{ __('crud.You do not have permission to view the table') }}</td>
                            </tr>
                    </tbody>
                </table>
                @endif
            </div>

            @if(hasPermission('PERMISSIONS.READ_ALL') || hasPermission('PERMISSIONS.READ') )
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ __('crud.Showing') }} {{ $permissions->firstItem() }} - {{ $permissions->lastItem() }} 
                    {{ __('crud.of') }} {{ $permissions->total() }} {{ __('crud.results') }}
                </div>
                {{ $permissions->links('layout.components.pagination') }}
            </div>
            @endif
        </div>
    </div>
</div>


@endsection

