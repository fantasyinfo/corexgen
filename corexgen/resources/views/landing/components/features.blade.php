    <!-- Features Section -->
    @php
        $featuresSection = $landingPage->where('key', 'features')->first()->toArray();

        $featuresSection = $featuresSection['value'] ?? [];
        $featuresOptions = $featuresSection['Options'] ?? [];
        // prePrintR($featuresOptions);
    @endphp
    <section id="features" class="features-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2> {{ $featuresSection['Heading'] ?? 'Powerful Features for Modern Businesses' }}</h2>
                <p class="lead">
                    {{ $featuresSection['SubHeading'] ?? 'CoreXGen offers comprehensive tools to manage your customer relationships' }}
                </p>
            </div>
            <div class="row">
                @foreach ($featuresOptions as $option)
                    <div class="col-md-4 mb-4">
                        <div class="feature-card text-center p-4">
                            <i class="bi bi-graph-up-arrow display-4 mb-3 text-primary"></i>
                            <h3>{{ $option['Heading'] ?? '' }}</h3>
                            <p>{{ $option['SubHeading'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
