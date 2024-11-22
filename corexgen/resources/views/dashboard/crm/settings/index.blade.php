@extends('layout.app')

@section('content')
 <div class="row">
            <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">{{__('settings.General')}}</a>
                    <a class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Profile</a>
                    <a class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Messages</a>
                    <a class="nav-link" id="v-pills-settings-tab" data-bs-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Settings</a>
                </div>
            </div>
            <div class="col-md-9 my-3">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel">
                        <h2>Home Content</h2>
                        <p>Welcome to the home tab. This is the default content displayed when the page loads.</p>
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
@endsection


@push('style')
<style>
    html.app-skin-dark .nav .nav-link.active, html.app-skin-dark .nav .nav-link:hover {
    color: #3454d1;
    background-color: #1c2438 !important;
    color: #eaebef !important;
}
.nav .nav-link.active, .nav .nav-link:hover {
    color: #001327;
    background-color: #eaebef;
    transition: all .3s ease;
}
</style>
@endpush
@push('scripts')
<script src="{{asset('assets/js/settings-init.min.js')}}"></script>
@endpush