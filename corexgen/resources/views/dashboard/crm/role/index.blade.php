@extends('layout.new.app')

@section('content')

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-2">
            <h5 class="card-title">{{ __('crm_role.Roles Management') }}</h5>
            <div class="card-header-action">
                @if(hasPermission('ROLE.CREATE'))
                <a data-toggle="tooltip" data-placement="top" title="Create New" href="{{ route('crm.role.create') }}" class="btn btn-md btn-primary me-2">
                    <i class="fas fa-plus"></i> <span>{{ __('crm_role.Create Role') }}</span>
                </a>
                @endif
                @if(hasPermission('ROLE.EXPORT'))
                <a data-toggle="tooltip" data-placement="top" title="Export Data" href="{{ route('crm.role.export', request()->all()) }}" class="btn btn-md btn-outline-secondary">
                    <i class="fas fa-download"></i> <span>{{ __('crud.Export') }}</span>
                </a>
                @endif
                @if(hasPermission('ROLE.IMPORT'))
                <button data-toggle="tooltip" data-placement="top" title="Import Data" data-bs-toggle="modal" data-bs-target="#bulkImportModal" class="btn btn-md btn-outline-info">
                    <i class="fas fa-upload"></i><span> {{ __('crud.Import') }}</span>
                </button>
                @endif
                @if(hasPermission('ROLE.FILTER'))
                <button data-toggle="tooltip" data-placement="top" title="Filter Data" onclick="openFilters()" class="btn btn-md btn-outline-warning">
                    <i class="fas fa-filter"></i>
                    <span>{{ __('crud.Filter') }}</span>
                </button>
                @endif
            </div>
        </div>

        <div class="card-body">
     
               
            @if(hasPermission('ROLE.FILTER'))
            <div id="filter-section">
              
                <div class="card-title">
                    {{ __('crud.Filter') }}
                
                </div>
                <!-- Advanced Filter Form -->
                <form action="{{ route('crm.role.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <!-- Search Input -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" 
                                       name="name" 
                                       class="form-control" 
                                       placeholder="{{ __('crm_role.Role Name') }}" 
                                       value="{{ request('name') }}">
                            </div>
                        </div>
                
                        <!-- Status Dropdown -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <select name="status" class="form-select">
                                    <option value="">{{ __('crm_role.All Statuses') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        {{ __('Active') }}
                                    </option>
                                    <option value="deactive" {{ request('status') == 'deactive' ? 'selected' : '' }}>
                                        {{ __('Inactive') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                
                        <!-- Buttons -->
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>{{ __('Search') }}
                                </button>
                                <button type="button" id="clearFilter" class="btn btn-light">
                                    <i class="fas fa-trash-alt me-1"></i>{{ __('Clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endif

   
            @if(hasPermission('ROLE.READ_ALL') || hasPermission('ROLE.READ') )
                <div class="table-responsive card">
                    @if($roles->isNotEmpty())
                    <table id="dbTable" class="table table-striped table-bordered ui celled">
                        <thead>
                            <tr>
                                <th> {{ __('crm_role.Role Name') }}</th>
                                <th>{{ __('crm_role.Description') }}</th>
                                <th>{{ __('crud.Status') }}</th>
                                <th>{{ __('crud.Created At') }}</th>
                    
                                <th class="text-end">{{ __('crud.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->role_name }}</td>
                                    <td>{{ Str::limit($role->role_desc, 50) }}</td>
                                    <td>
                                        <a data-toggle="tooltip" data-placement="top" title="{{$role->status == 'active' ? 'De Active' : 'Active'}}" href="{{ route('crm.role.changeStatus',['id' => $role->id] ) }}">
                                            <span class="badge {{ $role->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                                {{ ucfirst($role->status) }}
                                            </span>
                                        </a>
                                    </td>
                                    <td>{{ $role->created_at->format('d M Y') }}</td>
                                    @if(hasPermission('ROLE.UPDATE') || hasPermission('ROLE.DELETE'))
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            @if(hasPermission('ROLE.UPDATE'))
                                                <div class="edit-btn">
                                                    <a data-toggle="tooltip" data-placement="top"   title="{{ __('crud.Edit') }}" href="{{ route('crm.role.edit',['id' => $role->id] ) }}">
                                                    
                                                        <span class="text-warning"><i class="fas fa-pencil-alt"></i></span>
                                                    </a>
                                            
                                                </div>
                                            @endif

                                            @if(hasPermission('ROLE.DELETE'))
                                            <div class="delete-btn">
                                                <form action="{{ route('crm.role.destroy', ['id' => $role->id]) }}" method="POST" 
                                                    onsubmit="return confirm('{{ __('crud.Are you sure?') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm text-danger" data-toggle="tooltip" data-placement="top"  title="{{ __('crud.Delete') }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                            </div>
                                            @endif
                                            
                                        </div>
                                        
                                    </td>
                                    @else
                                    <td class="text-end">...</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="no-data-found">
                        <i class="far fa-clipboard"></i> 
                        <span class="mx-2">{{ __('crm_role.No Roles Found') }}</span>
                    </div>
                    @endif
                </div>

            @else
                {{-- no permissions to view --}}
                <div class="no-data-found">
                    <i class="fas fa-ban"></i> 
                    <span class="mx-2">{{ __('crud.You do not have permission to view the table') }}</span>
                </div>
            @endif





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