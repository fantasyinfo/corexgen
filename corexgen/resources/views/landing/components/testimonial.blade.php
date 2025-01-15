    <!-- Testimonials Section -->
    @php
        $testimonalSection = $landingPage->where('key', 'testimonials')->first()->toArray();

        $testimonalSection = $testimonalSection['value'] ?? [];
        $customOptions = $testimonalSection['Options'] ?? [];
        // prePrintR($customOptions);
    @endphp
    <section id="testimonials" class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>{{ $testimonalSection['Heading'] ?? 'What Our Customers Say' }}</h2>
            </div>
            <div class="row">
                @foreach ($customOptions as $c)
                    <div class="col-md-4 mb-4">
                        <div class="testimonial-card">
                            <p>"{{ $c['Message'] }}"</p>
                            <div class="customer-info">
                                <img src="{{ $c['LOGO'] }}" alt="Customer" class="customer-image">
                                <div>
                                    <h4>{{ $c['Customer Name'] }}</h4>
                                    <p>{{ $c['Position'] }}, {{ $c['Company'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
