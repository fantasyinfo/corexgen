 <!-- Solutions Section -->
 @php
     $solutionsSection = $landingPage->where('key', 'solutions')->first()->toArray();

     $solutionsSection = $solutionsSection['value'] ?? [];
     $solutionsOptions = $solutionsSection['Options'] ?? [];
     // prePrintR($featuresOptions);
 @endphp
 <section id="solutions" class="solutions-section py-5 bg-light">
     <div class="container">
         <div class="text-center mb-5">
             <h2>{{ $solutionsSection['Heading'] ?? 'Custom Solutions for Every Business' }}</h2>
             <p class="lead">
                 {{ $solutionsSection['SubHeading'] ?? 'CoreXGen adapts to your unique business needs with flexible, scalable solutions.' }}
             </p>
         </div>
         <div class="row align-items-center">
             @foreach ($solutionsOptions as $option)
                 <div class="col-md-3">
                     <div class="solution-list">
                         <div class="solution-item mb-3">
                             <i class="fas fa-check text-primary me-2"></i>
                             {{ $option }}
                         </div>
                     </div>
                 </div>
             @endforeach
         </div>
     </div>
 </section>
