<div class="modal fade" id="task-detail-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        @include('layout.header.css-links')
        @stack('style')
        <div id="footer-js-links" >
            @include('layout.footer.js-links')
        </div>
        {{-- @include('layout.footer.js-links') --}}
        <!-- Content Area -->
        {{-- <div class="content-area"> --}}
        <div class="toast-container" id="toastContainer"></div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        @yield('content')
        {{-- </div> --}}



    </div>
</div>
