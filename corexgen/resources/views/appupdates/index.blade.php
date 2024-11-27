@extends('layout.app')

@section('content')
    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5> {{ __('App Updates Management') }} </h5>
                </div>
                @if (hasPermission('APPUPDATES.CREATE'))
                    <div class="container py-4">
                        <div class="row justify-content-center border-dotted">
                            <div class="card-body">
                                <form action="{{ route(getPanelRoutes($module .'.create')) }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="form-group ">
                                                <label for="appupdate" class="form-label">Upload Updates File (ZIP)</label>
                                                <div class="input-group">
                                                    <input type="file"
                                                        class="form-control @error('appupdate') is-invalid @enderror"
                                                        id="appupdate" name="appupdate" accept=".zip">
                                                    @error('appupdate')
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
                                                    <i class="fas fa-plus me-2"></i>Upload Updates
                                                </button>
                                            
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="alert">
                <div class="alert-danger">
                    Todo : Please add curl request to check current version and is update available
                </div>
            </div>
        </div>
    </div>

    </div>
@endsection

