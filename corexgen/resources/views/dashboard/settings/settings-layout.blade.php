@extends('layout.app')

@push('style')
    <style>
        label {
            opacity: 0.7;
        }

        /* Mobile Sidebar Overlay */
        .settings-sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* z-index: 1049; */
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .settings-sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        .settings-container {
            display: flex;
            height: calc(100vh - 120px);
            background-color: var(--body-bg);
        }

        .settings-sidebar {
            width: 250px;
            background-color: var(--light-color);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            transition: all 0.3s ease;
            position: relative;
            /* z-index: 1050; */
        }

        /* Vertical Divider with Custom Colors */
        .settings-divider {
            width: 0.3px;
            background-color: #fff;
            opacity: 0.4;
            margin: 0 15px;
        }

        .settings-sidebar .nav-link {
            color: var(--body-color);
            padding: 0.75rem 1.25rem;
            border-left: 4px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            opacity: 0.7;
        }

        .settings-sidebar .nav-link i {
            margin-right: 10px;
            opacity: 0.7;
        }

        .settings-sidebar .nav-link:hover,
        .settings-sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: var(--body-color);
            opacity: 1;
            border-left-color: var(--light-color);
        }

        .settings-content {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            background-color: var(--card-bg);
            color: var(--body-color);
            position: relative;
        }

        /* Mobile Sidebar Toggle Button */
        .settings-mobile-toggle {
            display: none;
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1051;
            background-color: var(--primary-color);
            color: var(--light-color);
            border: none;
            padding: 10px;
            border-radius: 4px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .settings-container {
                height: auto;
            }

            .settings-sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100%;
                width: 250px;
                transition: left 0.3s ease;
                z-index: 1050;
            }

            .settings-sidebar.show {
                left: 0;
            }

            .settings-mobile-toggle {
                display: block;
            }

            .settings-divider {
                display: none;
            }

            .settings-content {
                padding-top: 60px;
            }
        }

        #loadingSpinner {
            position: fixed;
            top: 0;
            /* Changed from 50 to 0 */
            left: 0;
            /* Changed from 50 to 0 */
            width: 100%;
            height: 100%;
            /* Changed from 100vh for better cross-browser support */
            background-color: rgba(0, 0, 0, 0.5);
            /* Changed to darker overlay for better visibility */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div id="loadingSpinner" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex p-0">
                        <!-- Settings Main Sidebar -->
                        <div class="settings-sidebar">
                            <nav class="nav flex-column">
                                @foreach (SETTINGS_MENU_ITEMS as $key => $item)
                                    @if ($item['for'] === 'both')
                                        <a class="nav-link {{ request()->routeIs(getPanelRoutes('settings.' . $item['link'])) ? 'active' : '' }}"
                                            href="{{ route(getPanelRoutes($item['module'] . '.' . $item['link'])) }}">
                                            <i class="fas {{ $item['icon'] }}"></i> {{ $item['name'] }}
                                        </a>
                                    @elseif($item['for'] === 'tenant' && panelAccess() == PANEL_TYPES['SUPER_PANEL'])
                                        <a class="nav-link {{ request()->routeIs(getPanelRoutes($item['module'] . '.' . $item['link'])) ? 'active' : '' }}"
                                            href="{{ route(getPanelRoutes($item['module'] . '.' . $item['link'])) }}">
                                            <i class="fas {{ $item['icon'] }}"></i> {{ $item['name'] }}
                                        </a>
                                    @elseif ($item['for'] === 'company' && panelAccess() == PANEL_TYPES['COMPANY_PANEL'])
                                       
                                        <a class="nav-link {{ request()->routeIs(getPanelRoutes($item['module'] . '.' . $item['link'])) ? 'active' : '' }}"
                                            href="{{ route(getPanelRoutes($item['module'] . '.' . $item['link'])) }}">
                                            <i class="fas {{ $item['icon'] }}"></i> {{ $item['name'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </nav>
                        </div>

                        <!-- Vertical Divider -->
                        <div class="settings-divider"></div>

                        <!-- Settings Content Area -->
                        <div class="settings-content">
                            @yield('settings_content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        //     const settingsSidebarToggle = document.getElementById('settingsSidebarToggle');
        //     const settingsSidebar = document.getElementById('settingsSidebar');
        //     const settingsSidebarOverlay = document.getElementById('settingsSidebarOverlay');

        //     // Toggle sidebar on mobile
        //     settingsSidebarToggle.addEventListener('click', function() {
        //         settingsSidebar.classList.toggle('show');
        //         settingsSidebarOverlay.classList.toggle('show');
        //     });

        //     // Close sidebar when clicking overlay
        //     settingsSidebarOverlay.addEventListener('click', function() {
        //         settingsSidebar.classList.remove('show');
        //         settingsSidebarOverlay.classList.remove('show');
        //     });

        //     // Close sidebar when a nav link is clicked
        //     const sidebarLinks = settingsSidebar.querySelectorAll('.nav-link');
        //     sidebarLinks.forEach(link => {
        //         link.addEventListener('click', function() {
        //             if (window.innerWidth <= 768) {
        //                 settingsSidebar.classList.remove('show');
        //                 settingsSidebarOverlay.classList.remove('show');
        //             }
        //         });
        //     });

        //     // Responsive handling
        //     function handleResponsiveSidebar() {
        //         if (window.innerWidth > 768) {
        //             settingsSidebar.classList.remove('show');
        //             settingsSidebarOverlay.classList.remove('show');
        //         }
        //     }

        //     // Check on resize
        //     window.addEventListener('resize', handleResponsiveSidebar);
        // });
    </script>
@endpush
