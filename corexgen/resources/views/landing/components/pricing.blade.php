 <!-- Pricing Section -->
 @php
     $plansSection = $landingPage->where('key', 'plans')->first()->toArray();
     $plansSection = $plansSection['value'] ?? [];

     $currentSymbol = $settings->where('key', 'Panel Currency Symbol')->first()->value;
     $currentCode = $settings->where('key', 'Panel Currency Code')->first()->value;

 @endphp

 <style>
     .plan-card {
         border: none;
         border-radius: 12px;
         box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
         margin-bottom: 24px;
         transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
         overflow: hidden;
     }

     .plan-card:hover {
         transform: translateY(-10px);
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
     }

     .plan-card-header {
         background: linear-gradient(135deg, var(--primary-color), #6366f1);
         color: white;
         padding: 20px;
         text-align: center;
     }

     .plan-card-body {
         padding: 30px;
     }

     .plan-price-strike {
         font-size: 1rem;
         font-weight: 500;
         color: var(--primary-secondary);
         margin-bottom: 20px;
         text-align: center;
         text-decoration: line-through;
     }

     .plan-price {
         font-size: 2.5rem;
         font-weight: 700;
         color: var(--primary-color);
         margin-bottom: 20px;
     }

     .plan-features {
         border-top: 1px solid rgba(0, 0, 0, 0.07);
         padding-top: 20px;
     }

     .feature-item {
         display: flex;
         align-items: center;
         margin-bottom: 12px;
         color: var(--secondary-color);
     }

     .feature-icon {
         color: var(--accent-color);
         margin-right: 12px;
         font-size: 1.2rem;
     }

     .btn-plan-action {
         width: 100%;
         border-radius: 8px;
         padding: 12px;
         font-weight: 600;
         transition: all 0.3s ease;
     }
 </style>

 <section id="pricing" class="pricing-section py-5">
     <div class="container">
         <div class="text-center mb-5">
             <h2> {{ $plansSection['Heading'] ?? 'Simple, Transparent Pricing' }}</h2>
             <p class="lead">
                 {{ $plansSection['SubHeading'] ?? 'Choose a plan that grows with your business' }}</p>
         </div>
         <div class="row">
             @if (isset($plans) && $plans->isNotEmpty())
                 @foreach ($plans as $plan)
                     <div class="col-md-4">
                         <div class="card plan-card">
                             <div class="plan-card-header">
                                 <h3 class="mb-0">{{ $plan->name }} Plan</h3>
                                 <p>{{ $plan->desc }} </p>
                             </div>
                             <div class="plan-card-body">
                                 <div class="plan-price-strike">
                                     {{ $currentSymbol }} <span>{{ $plan->price }}
                                         ({{ $currentCode }})
                                     </span>
                                 </div>
                                 <div class="plan-price text-center">
                                     {{ $currentSymbol }} {{ $plan->offer_price }} <span class="text-muted"
                                         style="font-size: 1rem;">/{{ $plan->billing_cycle }}
                                         ({{ $currentCode }})</span>
                                 </div>
                                 @if ($plan->planFeatures)
                                     <div class="plan-features">
                                         @foreach ($plan->planFeatures as $features)
                                             @if ($features->value === -1)
                                                 <div class="feature-item">
                                                     <span class="feature-icon">✓</span>
                                                     Unlimited
                                                     {{ ucwords(replaceUnderscoreWithSpace($features->module_name)) }}
                                                 </div>
                                             @elseif($features->value > 0)
                                                 <div class="feature-item">
                                                     <span class="feature-icon">✓</span>
                                                     {{ number_format($features->value) }}
                                                     {{ ucwords(replaceUnderscoreWithSpace($features->module_name)) }}
                                                 </div>
                                             @elseif($features->value === 0)
                                                 <div class="feature-item text-muted">
                                                     <span class="feature-icon">✗</span>
                                                     {{ number_format($features->value) }}
                                                     {{ ucwords(replaceUnderscoreWithSpace($features->module_name)) }}
                                                 </div>
                                             @endif
                                         @endforeach
                                     </div>
                                 @endif
                                 <div class="text-center">
                                     <a href="/company/register" class="text-center  btn  mb-3 btn-primary">Get
                                         Started</a>
                                 </div>
                             </div>

                         </div>
                     </div>
                 @endforeach
             @endif
         </div>
     </div>
 </section>
