@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">{{ __('Users Management') }}</h5>
            <div class="card-header-action">
                <a href="{{ route('crm.users.create') }}" class="btn btn-md btn-primary me-2">
                    <i class="feather feather-plus"></i> <span>{{ __('Create User') }}</span>
                </a>
                <a href="{{ route('crm.users.export', request()->all()) }}" class="btn btn-md btn-outline-secondary">
                    <i class="feather feather-download"></i> <span>{{ __('Export') }}</span>
                </a>
                <button data-bs-toggle="modal" data-bs-target="#bulkImportModal" class="btn btn-md btn-outline-info">
                    <i class="feather feather-upload"></i><span> {{ __('Import') }}</span>
                </button>
                <button onclick="openFilters()" class="btn btn-md btn-light-brand">
                    <i class="feather-filter me-2"></i>
                    <span>{{ __('Filter') }}</span>
                </button>
            </div>
        </div>

        <div class="card-body">
     
               
     
            <div id="filter-section">
   <!-- Advanced Filter Form -->
   <form action="{{ route('crm.users.index') }}" method="GET" class="mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <input type="text" name="name" class="form-control" 
                   placeholder="{{ __('Name') }}" 
                   value="{{ request('name') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="email" class="form-control" 
                   placeholder="{{ __('Email') }}" 
                   value="{{ request('email') }}">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-control select2-hidden-accessible">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                    {{ __('Active') }}
                </option>
                <option value="deactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                    {{ __('Inactive') }}
                </option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control select2-hidden-accessible" name="role_id">
                <option value="">{{ __('Select Role') }}</option>
                @if($roles && $roles->isNotEmpty())
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                @else
                    <option disabled>No roles available</option>
                @endif
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">
                <i class="feather feather-search"></i>
            </button>
        </div>
    </div>
</form>
            </div>
         

            <!-- Roles Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery([
                                    'sort' => 'name', 
                                    'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'
                                ]) }}">
                                    {{ __('Name') }}
                                    @if(request('sort') == 'name')
                                        {!! request('direction') == 'asc' ? '&#9650;' : '&#9660;' !!}
                                    @endif
                                </a>
                            </th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created At') }}</th>
                   
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role_name }}</td>
                                <td>
                                    <a href="{{ route('crm.users.changeStatus',['id' => $user->id] ) }}">
                                        <span class="badge {{ $user->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </a>
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                 

                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md ms-auto" data-bs-toggle="dropdown" data-bs-offset="0,28" aria-expanded="false">
                                            <i class="feather feather-more-horizontal"></i>
                                        </a>
                                        <ul class="dropdown-menu" style="">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('crm.users.edit',['id' => $user->id] ) }}">
                                                    <i class="feather feather-edit-3 me-3"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                            </li>
                                            
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('crm.users.destroy', ['id' => $user->id]) }}" method="POST" 
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
                                <td colspan="5" class="text-center"><i class="fa-solid fa-file"></i> {{ __('No roles found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ __('Showing') }} {{ $users->firstItem() }} - {{ $users->lastItem() }} 
                    {{ __('of') }} {{ $users->total() }} {{ __('results') }}
                </div>
                {{ $users->links('layout.components.pagination') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="bulkImportForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkImportModalLabel">Bulk Import Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Upload CSV File</label>
                        <div class="drop-zone" style="border: 2px dashed #ddd; padding: 20px; text-align: center;">
                            <input type="file" name="file" id="csvFile" class="form-control" accept=".csv" style="display: none;" />
                            <p>Drag & Drop your file here or click to browse</p>
                        </div>
                        <small class="form-text text-muted">Only CSV files are allowed. Max size: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection


@push('style')
<style>
    #filter-section{
        display:none;
    }
</style>
@endpush

@push('scripts')
<script>

function openFilters() {
    const filterSection = document.getElementById('filter-section');
    if (filterSection.style.display === 'block') {
        filterSection.style.display = 'none';
    } else {
        filterSection.style.display = 'block';
    }
}

document.querySelector('.drop-zone').addEventListener('click', function () {
        document.querySelector('#csvFile').click();
    });

    document.querySelector('#csvFile').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            this.nextElementSibling.textContent = file.name;
        }
    });

    document.querySelector('#bulkImportForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const response = await fetch('{{ route('crm.users.import') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Import failed. Please check the file format.');
        }
    });
</script>
@endpush