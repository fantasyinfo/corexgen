    @include('layout.header.header')


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
