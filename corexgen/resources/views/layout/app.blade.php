    @include('layout.header.header')


    <body class="sidebar-collapsed">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay"></div>
        <!-- Sidebar -->
        @include('layout.sidebar.sidebar')

        <!-- Main Content -->
        <div class="main-content">


            @include('layout.header.sub-header')



            <div id="task-detail-container"></div>
            <!-- Content Area -->
            <div class="content-area">
                <!-- breadcrum section -->
                @include('layout.components.alert')
                @include('layout.components.alert-modal')
                @include('layout.components.delete-confirm')
                @include('layout.components.bulk-delete-confirm')
                @include('layout.components.delete-success')
                @include('layout.components.change-password')

                @if (config('app.app_status') == 'demo')
                    @include('layout.components.demo-alert')
                @endif


                <div class="toast-container" id="toastContainer"></div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="javascript:history.back()" class="btn-link">
                                <i class="fas fa-arrow-left"></i>
                                {{ __('navbar.Back') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">{{ __('navbar.Home') }}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title ?? '' }}</li>
                    </ol>
                </nav>


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
