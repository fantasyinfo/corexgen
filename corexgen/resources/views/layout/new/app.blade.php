    @include('layout.new.header.header')

  <body class="sidebar-collapsed">
   <!-- Sidebar Overlay for Mobile -->
   <div class="sidebar-overlay"></div>
   <!-- Sidebar -->
    @include('layout.new.sidebar.sidebar')

   <!-- Main Content -->
   <div class="main-content">
    

    @include('layout.new.header.sub-header')

       
       <!-- Content Area -->
       <div class="content-area">
         <!-- breadcrum section -->

         <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">{{__('navbar.Home')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title ? $title : ''}}</li>
          </ol>
      </nav>

          @include('layout.new.components.alert')
          
         <div class="toast-container" id="toastContainer"></div>

            @yield('content')
       </div>

       @include('layout.new.footer.footer')
   </div>

   @include('layout.new.footer.js-links')
  </body>
</html>
