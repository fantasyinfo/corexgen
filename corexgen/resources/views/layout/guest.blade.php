    @include('layout.header.header')

<style>
    @media (max-width: 768px){
        .content-area {
            display: block !important;
        }
    }
</style>
    <body>


        <!-- Main Content -->
        <div>





            <!-- Content Area -->
            <div class="content-area">
                <!-- breadcrum section -->
                @include('layout.components.alert')
                @include('layout.components.alert-modal')

                @include('layout.components.delete-success')



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
            </div>

 
        </div>

        @include('layout.footer.js-links')
    </body>

    </html>
