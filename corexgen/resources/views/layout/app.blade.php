    @include('layout.header.header')


    <body class="sidebar-collapsed">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay"></div>
        <!-- Sidebar -->
        @include('layout.sidebar.sidebar')

        <!-- Main Content -->
        <div class="main-content">


            @include('layout.header.sub-header')


            <!-- Content Area -->
            <div class="content-area">
                <!-- breadcrum section -->
                @include('layout.components.alert')
                @include('layout.components.alert-modal')
                @include('layout.components.delete-confirm')
                @include('layout.components.bulk-delete-confirm')
                @include('layout.components.delete-success')
                @include('layout.components.change-password')


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

            @include('layout.footer.footer')
        </div>

        @include('layout.footer.js-links')
    </body>

    </html>
