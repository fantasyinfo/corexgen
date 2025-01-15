   <!-- Hero Section -->
   @php
       $heroSection = $landingPage->where('key', 'hero')->first()->toArray();
    //    prePrintR($heroSection);
       $heroSection = $heroSection['value'] ?? [];
   @endphp
   <header id="home" class="hero-section text-center">
       <div class="container">
           <div class="row justify-content-center">
               <div class="col-md-10">
                   <h1 class="display-4 mb-4">
                       {{ $heroSection['Heading'] ?? 'Streamline Your Business with CoreXGen CRM' }}</h1>
                   <p class="lead mb-5">
                       {{ $heroSection['SubHeading'] ??
                           "Intelligent Customer Relationship Management that transforms how you connect,
                                           engage, and grow your business." }}
                   </p>

                   <div class="hero-cta">
                       <a href="/company/register" class="btn btn-primary btn-lg me-3">Register</a>
                       <a href="#features" class="btn btn-outline-secondary btn-lg">See Features</a>
                   </div>
               </div>
           </div>
       </div>
   </header>
