
    @include('layout.header.header')
   

        <div class="nxl-content">
         
       
            @include('layout.header.page')
            <div class="main-content">
              <div class="row">
                @include('layout.components.alert')
                @yield('content')
              </div>
            </div>
       
        </div>

 @include('layout.footer.footer')