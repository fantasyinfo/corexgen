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
                <button data-bs-toggle="modal" data-bs-target="#bulkImportModal" class="btn btn-outline-info">
                    <i class="feather feather-upload"></i> {{ __('Import') }}
                </button>
            </div>
        </div>

        <div class="card-body">
     
                <h6>{{__('Filters')}}</h6>
     
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
                                    <a href="{{ route('crm.role.changeStatus',['id' => $role->id] ) }}">
                                        <span class="badge {{ $role->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($role->status) }}
                                        </span>
                                    </a>
                                </td>
                                <td>{{ $role->created_at->format('d M Y') }}</td>
                                <td class="text-end">
                                 

                                    <div class="dropdown">
                                        <a href="javascript:void(0)" class="avatar-text avatar-md ms-auto" data-bs-toggle="dropdown" data-bs-offset="0,28" aria-expanded="false">
                                            <i class="feather feather-more-horizontal"></i>
                                        </a>
                                        <ul class="dropdown-menu" style="">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('crm.role.edit',['id' => $role->id] ) }}">
                                                    <i class="feather feather-edit-3 me-3"></i>
                                                    <span>{{ __('Edit') }}</span>
                                                </a>
                                            </li>
                                            
                                            <li class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('crm.role.destroy', ['id' => $role->id]) }}" method="POST" 
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
                    {{ __('Showing') }} {{ $roles->firstItem() }} - {{ $roles->lastItem() }} 
                    {{ __('of') }} {{ $roles->total() }} {{ __('results') }}
                </div>
                {{ $roles->links('layout.components.pagination') }}
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
                    <h5 class="modal-title" id="bulkImportModalLabel">Bulk Import Roles</h5>
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

@push('scripts')
<script>

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
        const response = await fetch('{{ route('crm.role.import') }}', {
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