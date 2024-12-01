@extends('layout.app')

@section('content')
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5> {{ __('Modules Management') }} </h5>
                </div>
                @if (hasPermission('MODULES.CREATE'))
                    <div class="container py-4">
                        <div class="row justify-content-center border-dotted">
                            <div class="card-body">
                                <form action="{{ route(getPanelRoutes($module .'.create')) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="form-group ">
                                                <label for="module" class="form-label">Upload Module File (ZIP)</label>
                                                <div class="input-group">
                                                    <input type="file"
                                                        class="form-control @error('module') is-invalid @enderror"
                                                        id="module" name="module" accept=".zip">
                                                    @error('module')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                                <div class="form-text">Only ZIP files are accepted</div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                           
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Upload Module
                                                </button>
                                            
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <hr>
            <div class="card-body">
                @if (hasPermission('MODULES.READ_ALL') || hasPermission('MODULES.READ'))
                    <div class="table-responsive card">

                        <table id="userTable" class="table table-striped table-bordered ui celled">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 border-b">{{ __('Name') }}</th>
                                    <th class="px-6 py-3 border-b">{{ __('Version') }}</th>
                                    <th class="px-6 py-3 border-b">{{ __('Description') }}</th>
                                    <th class="px-6 py-3 border-b">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 border-b">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modules as $module1)
                                    <tr>
                                        <td class="px-6 py-4 border-b">{{ $module1->name }}</td>
                                        <td class="px-6 py-4 border-b">{{ $module1->version }}</td>
                                        <td class="px-6 py-4 border-b">{{ $module1->description }}</td>
                                        <td class="px-6 py-4 border-b">{{ ucfirst($module1->status) }}</td>
                                        <td class="px-6 py-4 border-b">
                                            @if (hasPermission('MODULES.CREATE'))
                                                <form action="{{ route(getPanelRoutes($module .'.destroy'), $module1->name) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" data-toggle="tooltip"
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to uninstall this module?')">
                                                        <i class="fas fa-trash-alt"></i> Uninstall
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


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

    </div>
@endsection


@push('scripts')
    <script>
        $("#userTable").DataTable();
    </script>
@endpush
