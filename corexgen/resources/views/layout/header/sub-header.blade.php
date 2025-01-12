<header class="header shadow-sm">
    <div class="container-fluid">
        <div class="row align-items-center">
            <!-- Left Section -->
            <div class="col-12 col-md-6 d-flex align-items-center flex-wrap">
                <div class="toggle-btn me-2" id="sidebarToggle">
                    <i class="fas fa-bars fa-lg"></i>
                </div>
             
            </div>
            <!-- Right Section -->
            <div class="col-12 col-md-6 d-flex justify-content-end align-items-center flex-wrap">
                @if (session()->get('login_as') == true)
                    <div class="me-2 mb-2 mb-md-0">
                        <a href="{{ route(getPanelRoutes('users.loginback')) }}" class="btn btn-primary btn-sm">
                            Login Back to Super Panel
                        </a>
                    </div>
                @endif
                <div class="theme-toggle mx-2" id="themeToggle" data-toggle="tooltip" title="Theme Toggle">
                    <i class="fas fa-sun fa-lg"></i>
                </div>
                <div class="dropdown mx-2">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                        id="langDropDown" data-bs-toggle="dropdown" aria-expanded="false" data-toggle="tooltip" title="Change Language">
                        <i class="fas fa-language"></i>
                        <span class="badge text-success">{{ ucwords(App::getLocale()) }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="langDropDown">
                        <li><a class="dropdown-item" href="{{ url('/setlang/en') }}">
                                <span class="badge text-success">En</span> {{ __('general.English') }}</a></li>
                        <li><a class="dropdown-item" href="{{ url('/setlang/hi') }}">
                                <span class="badge text-info">Hi</span> {{ __('general.Hindi') }}</a></li>
                    </ul>
                </div>
                <div class="dropdown mx-2">
                    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">

                        <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . (auth()->user()->profile_photo_path ?? 'avatars/default.webp'))" />

                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li>
                            <span
                                class="badge bg-soft-success text-success ms-1">{{ getRoleName(auth()->user()->role_id) }}</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route(getPanelRoutes('users.profile')) }}">
                                <i class="fas fa-user me-2"></i> {{ __('general.Profile') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route(getPanelRoutes('settings.general')) }}">
                                <i class="fas fa-cog me-2"></i> {{ __('general.Settings') }}</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> {{ __('general.Logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
