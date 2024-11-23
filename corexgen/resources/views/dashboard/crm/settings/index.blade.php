@extends('layout.app')

@section('content')
<div class="container-fluid">
    <div class="card">

 
 <div class="row " id="settingsPage">
            <div class="col-md-3 tabSideBar">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">  <i class="fas fa-angle-double-right"></i> {{__('settings.General')}}</a>
                    <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">  <i class="fas fa-angle-double-right"></i> Profile</a>
                    <a class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">  <i class="fas fa-angle-double-right"></i> Messages</a>
                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">  <i class="fas fa-angle-double-right"></i> Settings</a>
                </div>
            </div>
            <div class="col-md-9 my-3">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel">
                        <h2>{{__('General Settings')}}</h2>
                        {{-- $general_settings --}}
                        <div class="justify-content-md-center ">
                            <div class="card stretch stretch-full">
                                <form id="generalSettingsForm" action="{{ route('crm.settings.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="card-body general-info">
                                        <div class="mb-5 d-flex align-items-center justify-content-between">
                                            <h5 class="fw-bold mb-0 me-4">
                                                <span class="d-block mb-2">{{ __('settings.General Settings') }}</span>
                                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                            </h5>
                                            @if (hasPermission('SETTINGS.UPDATE'))
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> <span>{{ __('settings.Save') }}</span>
                                            </button>
                                            @endif
                                        </div>
                                        
                                        @if($general_settings->isNotEmpty())
                                            @foreach($general_settings as $gs)
                                                <div class="row mb-4 align-items-center">
                                                    <div class="col-lg-4">
                                                        <label for="{{ $gs->key }}" class="fw-semibold">{{ $gs->key }}: </label>
                                                    </div>
                                                    <div class="col-lg-8">
                                                        <div class="input-group">
                                                
                                                            @if($gs->is_media_setting && $gs->input_type == 'image')
                                                                <input type="file" value="{{ $gs->value }}" name="{{ $gs->key }}" id="{{ $gs->key }}"/>
                                                            @elseif($gs->input_type == 'text')
                                                                <input type="text" 
                                                                class="form-control @error('{{ $gs->key }}') is-invalid @enderror" 
                                                                id="{{ $gs->key }}" 
                                                                name="{{ $gs->key }}" 
                                                                value="{{ $gs->value }}"
                                                                >
                                                            @elseif($gs->input_type == 'time')
                                                                <input type="time" 
                                                                class="form-control @error('{{ $gs->key }}') is-invalid @enderror" 
                                                                id="{{ $gs->key }}" 
                                                                name="{{ $gs->key }}" 
                                                                value="{{ $gs->value }}"
                                                                >
                                                            @elseif($gs->input_type == 'date')
                                                                <input type="date" 
                                                                class="form-control @error('{{ $gs->key }}') is-invalid @enderror" 
                                                                id="{{ $gs->key }}" 
                                                                name="{{ $gs->key }}" 
                                                                value="{{ $gs->value }}"
                                                                >
                                                            @endif
                                                            
                                                            <div class="invalid-feedback" id="roleNameError">
                                                                @error('{{ $gs->key }}')
                                                                    {{ $message }}
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
    
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel">
                        <h2>Profile Content</h2>
                        <p>Here you can find detailed information about the user profile.</p>
                    </div>
                    <div class="tab-pane fade" id="v-pills-messages" role="tabpanel">
                        <h2>Messages Content</h2>
                        <p>View and manage your messages in this section.</p>
                    </div>
                    <div class="tab-pane fade" id="v-pills-settings" role="tabpanel">
                        <h2>Settings Content</h2>
                        <p>Customize your application settings here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

