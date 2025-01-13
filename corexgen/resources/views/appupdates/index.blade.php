@extends('layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header ">
                <h5>{{ __('App Updates Management') }}</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Current Version:</strong> {{ $currentVersion }} <strong>Latest Version:</strong> {{ $latestVersion }}
                </div>

                @if ($latestVersion && is_string($latestVersion))
                    @if ($isUpdateAvailable)
                        <div class="alert alert-warning">
                            A new version ({{ $latestVersion }}) is available! Please update your application.
                        </div>
                    @else
                        <div class="alert alert-success">
                            You are using the latest version of the application.
                        </div>
                    @endif
                @else
                    <div class="alert alert-danger">
                        Unable to fetch the latest version. Please try again later.
                    </div>
                @endif

                @if (hasPermission('APPUPDATES.CREATE'))
                    <div class="border p-4 mt-3">
                        <form action="{{ route(getPanelRoutes($module . '.create')) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="appupdate" class="form-label">Upload Updates File (ZIP)</label>
                                        <input type="file" class="form-control @error('appupdate') is-invalid @enderror" id="appupdate" name="appupdate" accept=".zip">
                                        @error('appupdate')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                        <small class="form-text text-muted">Only ZIP files are accepted.</small>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-upload me-2"></i> Upload Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
            <div class="card-footer text-muted">
                <small>Ensure that the uploaded update file is valid and secure.</small>
            </div>
        </div>
    </div>
@endsection
