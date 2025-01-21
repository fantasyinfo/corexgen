   <!-- boostrap css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap/bootstrap.min.css') }}" />

   <!-- d-select-dropdown-library-forsearch -->
   {{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/dselect/dselect.min.css')}}" /> --}}
   <link rel="stylesheet" type="text/css" href="{{ asset('css/select2/select2.min.css') }}" />
   <link rel="stylesheet" type="text/css" href="{{ asset('css/flatpicker/flatpicker.min.css') }}" />

   <!-- datatables css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('css/datatables/datatables.min.css') }}" />

   <!-- fontawesome css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('css/fontawesome/css/all.min.css') }}" />

   <!-- feathericons css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('css/feather-icons/css/feathericon.min.css') }}" />

   <!-- custom css -->
   <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/colors.css') }}" />
   <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/style.css') }}" />
   <link rel="stylesheet" type="text/css" href="{{ asset('css/custom/custom.css') }}" />



   <!-- Dynamically Inject Theme CSS Variables -->
   @if (isset($themeSettings) && Auth::check())
   <style>
       @if (Auth::user()->is_tenant)
           /* Tenant Theme Variables */
           :root {
               @foreach ($themeSettings as $colors)
                   @php
                       // Strip '-company' suffix if it exists
                       $name = Str::replaceLast('-company', '', $colors['name']);
                   @endphp

                   @if (!Str::contains($name, '-d'))
                       --{{ $name }}: {{ $colors['value'] }};
                   @endif
               @endforeach
           }

           [data-bs-theme="dark"] {
               @foreach ($themeSettings as $colors)
                   @php
                       // Strip '-company' suffix if it exists
                       $name = Str::replaceLast('-company', '', $colors['name']);
                   @endphp

                   @if (Str::contains($name, '-d'))
                       --{{ Str::replaceLast('-d', '', $name) }}: {{ $colors['value'] }};
                   @endif
               @endforeach
           }
       @elseif (Auth::user()->company_id)
           /* Company Theme Variables */
           :root {
               @foreach ($themeSettings as $colors)
                   @php
                       // Strip '-company' suffix if it exists
                       $name = Str::replaceLast('-company', '', $colors['name']);
                   @endphp

                   @if (!Str::contains($name, '-d'))
                       --{{ $name }}: {{ $colors['value'] }};
                   @endif
               @endforeach
           }

           [data-bs-theme="dark"] {
               @foreach ($themeSettings as $colors)
                   @php
                       // Strip '-company' suffix if it exists
                       $name = Str::replaceLast('-company', '', $colors['name']);
                   @endphp

                   @if (Str::contains($name, '-d'))
                       --{{ Str::replaceLast('-d', '', $name) }}: {{ $colors['value'] }};
                   @endif
               @endforeach
           }
       @endif
   </style>
@endif

