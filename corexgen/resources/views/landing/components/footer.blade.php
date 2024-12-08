    <!-- Footer -->
    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <img src="corexgen-logo-white.png" alt="CoreXGen Logo" height="40" class="mb-3">
                    <p>Intelligent CRM that helps businesses grow and succeed.</p>
                </div>
                <div class="col-md-3">
                    <h4>Product</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Integrations</a></li>
                        <li><a href="#">Release Notes</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h4>Company</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h4>Support</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Status</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom text-center mt-4 pt-3">
                <p>&copy; 2024 CoreXGen. All Rights Reserved.</p>
                <div class="social-icons">
                    <a href="#" class="mx-2"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="mx-2"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="mx-2"><i class="bi bi-facebook"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{asset('js/jquery/jquery.min.js')}}"></script>
    <!-- bootstrap js -->
    <script src="{{asset('js/boostrap/bootstrap.bundle.min.js')}}"></script>
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