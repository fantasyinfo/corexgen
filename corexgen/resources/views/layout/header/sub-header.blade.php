<header class="header shadow-sm">
    <div class="d-flex justify-content-between align-items-center w-100">
        <div class="d-flex align-items-center">
            <div class="toggle-btn" id="sidebarToggle">
                <i class="fas fa-bars fa-lg"></i>
            </div>

            <span class="badge badge-pill badge-primary">
                @if (Auth::user()->is_tenant && Auth::user()->role_id == null)
                    Super Admin
                @elseif(Auth::user()->is_tenant && Auth::user()->role_id != null)
                    Super Employee
                @elseif(!Auth::user()->is_tenant && Auth::user()->role_id == null)
                    Company Admin
                @elseif(!Auth::user()->is_tenant && Auth::user()->role_id != null)
                    Company Employee
                @endif

                {{ getCompanyName() }}
            </span>

        </div>
        <div class="d-flex align-items-center">
            @if (session()->get('login_as') == true)
                <div>
                    <a href="{{ route(getPanelRoutes('users.loginback')) }}" class="btn btn-primary btn-sm">Login Back to
                        Super Panel</a>
                </div>
            @endif
            <div class=" mx-1 theme-toggle" id="themeToggle">
                <i class="fas fa-sun fa-lg"></i>
            </div>
            <div class="divider-v"></div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                    id="langDropDown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-language"></i> <span
                        class="badge text-success">{{ ucwords(App::getLocale()) }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="langDropDown">
                    <li><a class="dropdown-item" href="{{ url('/setlang/en') }}"><span
                                class="badge text-success">En</span> {{ __('general.English') }}</a></li>
                    <li><a class="dropdown-item" href="{{ url('/setlang/hi') }}"><span class="badge text-info">Hi</span>
                            {{ __('general.Hindi') }}</a></li>
                </ul>
            </div>
            <div class="divider-v"></div>
            <div class=" mx-1 dropdown">
                <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ ucwords(auth()->user()->name) }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    <li><span
                            class="badge bg-soft-success text-success ms-1">{{ getRoleName(auth()->user()->role_id) }}</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="{{ route(getPanelRoutes('users.profile')) }}"><i
                                class="fas fa-user me-2"></i>
                            {{ __('general.Profile') }}</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>
                            {{ __('general.Settings') }}</a></li>
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
</header>
