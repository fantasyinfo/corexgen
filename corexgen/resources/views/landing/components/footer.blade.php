    <!-- Footer -->
    @php
        $footerSection = $landingPage->where('key', 'footer')->first()->toArray();

        $footerSection = $footerSection['value'] ?? [];

        // prePrintR($customOptions);

    @endphp
    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto text-center">
                    <img src="{{ $logo }}" alt="CoreXGen Logo" height="40" class="mb-3">
                    <p>{{ $footerSection['Heading'] ?? 'Intelligent CRM that helps businesses grow and succeed.' }}</p>
                </div>
            </div>
            <div class="footer-bottom text-center mt-4">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}.
                    {{ $footerSection['SubHeading'] ?? 'All Rights Reserved.' }}</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('js/boostrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Custom JS -->
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        themeToggle.addEventListener('click', () => {
            if (body.getAttribute('data-bs-theme') === 'light') {
                body.setAttribute('data-bs-theme', 'dark');
            } else {
                body.setAttribute('data-bs-theme', 'light');
            }
        });
    </script>
