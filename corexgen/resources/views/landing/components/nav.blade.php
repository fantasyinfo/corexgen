  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
      <div class="container">
          <a class="navbar-brand" href="#">
              <img src="{{ $logo }}" alt="CoreXGen Logo" height="40">
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
              <ul class="navbar-nav mx-auto">
                  <li class="nav-item"><a class="nav-link" href="{{ config('app.url') }}#home">Home</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ config('app.url') }}#features">Features</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ config('app.url') }}#solutions">Solutions</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ config('app.url') }}#pricing">Pricing</a></li>
              </ul>
              <div class="navbar-nav">
                  <a href="/login" class="btn btn-primary me-2">Login</a>
                  <a href="/company/register" class="btn btn-outline-secondary">Register</a>
              </div>
          </div>
      </div>
  </nav>
