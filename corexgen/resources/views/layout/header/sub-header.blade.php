<header class="header shadow-sm py-2">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Main Header Content -->
                <div class="d-flex align-items-center justify-content-between">
                    <!-- Left Section: Toggle, Search & Quick Create -->
                    <div class="d-flex align-items-center flex-grow-1">
                        <!-- Sidebar Toggle -->
                        <button class="btn btn-link text-secondary p-0 me-3" id="sidebarToggle">
                            <i class="fas fa-bars fa-lg"></i>
                        </button>

                        <!-- Search Bar - Desktop & Mobile -->
                        <!-- Search Bar - Desktop & Mobile -->
                        <div class="search-wrapper flex-grow-1 me-3 d-flex">
                            <form class="w-100" method="GET" action="{{ route(getPanelRoutes('search')) }}"
                                id="searchForm">
                                <!-- Desktop Search -->
                                <div class="input-group d-none d-lg-flex">
                                    <input class="form-control" type="search" name="q"
                                        placeholder="{{ panelAccess() == PANEL_TYPES['SUPER_PANEL'] ? 'Search Companies, Users, Plans ...' : 'Search Leads, Clients, Tasks, Users, Invoices ...' }}"
                                        value="{{ request('q') }}" />
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>

                                <!-- Mobile Search Button -->
                                <button class="btn btn-link text-secondary p-0 d-lg-none position-relative"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearchOverlay"
                                    aria-expanded="false" aria-controls="mobileSearchOverlay">
                                    <i class="fas fa-search fa-lg"></i>
                                </button>

                                <!-- Mobile Search Overlay -->
                                <div class="collapse position-absolute start-0 end-0 top-100 p-3 shadow bg-white"
                                    id="mobileSearchOverlay">
                                    <div class="input-group">
                                        <input class="form-control" type="search" name="mobile_q"
                                            placeholder="Search..." value="{{ request('q') }}" />
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>


                        <!-- Quick Create Dropdown -->
                        <div class="dropdown">
                            <button title="Quick Create" data-toggle="tooltip"
                                class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                id="quickCreateDropDown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <ul class="dropdown-menu shadow" aria-labelledby="quickCreateDropDown">
                                @php
                                    // Determine which menu to display
                                    $menuType =
                                        panelAccess() == PANEL_TYPES['SUPER_PANEL'] ? 'SUPER_PANEL' : 'COMPANY_PANEL';
                                    $menuItems = QUICK_CREATE_MENU[$menuType]['MENUS'];
                                @endphp

                                @foreach ($menuItems as $menu)
                                    <li>
                                        <a class="dropdown-item" href="{{ route(getPanelRoutes($menu['route'])) }}">
                                            <i class="fas fa-plus-circle"></i> {{ $menu['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                        </div>
                    </div>

                    <!-- Right Section: Actions & Profile -->
                    <div class="d-flex align-items-center ms-3">
                        @if (session()->get('login_as') == true)
                            <a href="{{ route(getPanelRoutes('users.loginback')) }}"
                                class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-user-shield"></i>
                                <span class="d-none d-sm-inline">Login Back</span>
                            </a>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center">
                            <!-- Fullscreen Toggle -->
                            <button title="Full Screen" data-toggle="tooltip"
                                class="btn btn-link text-secondary p-0 me-3 d-none d-md-block" id="fullscreenToggle"
                                onclick="toggleFullscreen()">
                                <i class="fas fa-expand fa-lg"></i>
                            </button>

                            <!-- Theme Toggle -->
                            <button title="Theme toggle" data-toggle="tooltip"
                                class="btn btn-link text-secondary p-0 me-3" id="themeToggle">
                                <i class="fas fa-sun fa-lg"></i>
                            </button>

                            <!-- Language Dropdown -->
                            <div class="dropdown me-3">
                                <button title="Language" data-toggle="tooltip" class="btn btn-link text-secondary p-0"
                                    type="button" id="langDropDown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-language fa-lg"></i>
                                    <span class="badge bg-success">{{ ucwords(App::getLocale()) }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="{{ url('/setlang/en') }}">
                                            <span class="badge bg-success">En</span> {{ __('general.English') }}</a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ url('/setlang/hi') }}">
                                            <span class="badge bg-info">Hi</span> {{ __('general.Hindi') }}</a></li>
                                </ul>
                            </div>

                            <!-- User Profile Dropdown -->
                            <div class="dropdown">
                                <a href="#" class="d-flex align-items-center text-dark text-decoration-none"
                                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <x-form-components.profile-avatar :hw="32" :url="asset(
                                        'storage/' . (auth()->user()->profile_photo_path ?? 'avatars/default.webp'),
                                    )" />
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><span class="dropdown-item-text">
                                            <span
                                                class="badge bg-soft-success text-success">{{ getRoleName(auth()->user()->role_id) }}</span>
                                        </span></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route(getPanelRoutes('users.profile')) }}">
                                            <i class="fas fa-user me-2"></i> {{ __('general.Profile') }}</a></li>
                                    <li><a class="dropdown-item"
                                            href="{{ route(getPanelRoutes('settings.general')) }}">
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
            </div>
        </div>
    </div>


</header>

<style>
    /* Core styles */
    .header {
        position: relative;
    }

    /* Mobile optimizations */
    @media (max-width: 991.98px) {
        .header .btn {
            padding: 0.25rem 0.5rem;
        }

        .header .badge {
            font-size: 0.75rem;
        }
    }

    /* Animation for mobile search overlay */
    #mobileSearchOverlay {
        transition: all 0.3s ease-in-out;
        z-index: 1000;
    }

    #mobileSearchOverlay.collapsing {
        transform: translateY(-10px);
    }

    #mobileSearchOverlay.show {
        transform: translateY(0);
    }

    /* Improved touch targets for mobile */
    @media (max-width: 575.98px) {

        .header .btn,
        .header .dropdown-toggle {
            min-height: 35px;
            min-width: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }
</style>

<script>
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            document.getElementById('fullscreenToggle').innerHTML = '<i class="fas fa-compress fa-lg"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                document.getElementById('fullscreenToggle').innerHTML = '<i class="fas fa-expand fa-lg"></i>';
            }
        }

        // Close mobile overlay when clicking outside
        document.addEventListener('click', function(e) {
            const mobileOverlay = document.getElementById('mobileSearchOverlay');
            if (!mobileOverlay.contains(e.target) && mobileOverlay.classList.contains('show')) {
                bootstrap.Collapse.getInstance(mobileOverlay).hide();
            }
        });

        // Ensure only one "q" parameter is submitted
        document.getElementById('searchForm').addEventListener('submit', function() {
            const mobileInput = this.querySelector('input[name="mobile_q"]');
            const desktopInput = this.querySelector('input[name="q"]');

            if (mobileInput && mobileInput.value.trim()) {
                desktopInput.disabled = true; // Avoid duplicate "q"
            }
        });
    }
</script>
