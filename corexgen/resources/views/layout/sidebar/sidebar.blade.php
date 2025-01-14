@php

    $menus = getCRMMenus();
    $currentRoute = Route::currentRouteName();

    // Function to get the base route without prefixes for comparison

    if (!function_exists('_getBaseRouteCoreX')) {
        function _getBaseRouteCoreX($route)
        {
            $prefix = getPanelRoutes(''); // Get the dynamic panel prefix (e.g., 'company-panel.')
            $cleanedRoute = str_replace($prefix, '', $route); // Remove the prefix from the route
            return trim($cleanedRoute, '.'); // Ensure no trailing dots
        }
    }

    // Function to check if the current route matches or is part of the parent menu's URL
if (!function_exists('isParentMenuActive')) {
    function isParentMenuActive($currentRoute, $parentMenuUrl)
    {
        // Return false if menu_url is empty
        if (empty($parentMenuUrl)) {
            // Log::warning('Menu URL is empty', ['currentRoute' => $currentRoute, 'parentMenuUrl' => $parentMenuUrl]);
            return false;
        }

        $baseCurrentRoute = _getBaseRouteCoreX($currentRoute); // Get the base route for the current route
        // Log::info('Checking if parent menu is active', [
        //     'currentRoute' => $currentRoute,
        //     'baseCurrentRoute' => $baseCurrentRoute,
        //     'parentMenuUrl' => $parentMenuUrl,
        //     'isActive' => str_contains($baseCurrentRoute, $parentMenuUrl),
        // ]);
        return str_contains($baseCurrentRoute, $parentMenuUrl); // Check if the base route contains the parent menu's URL
        }
    }
@endphp

<style>
    .pro {
        border: 1px dotted red;
        padding: 3px 10px;
        color: var(--primary-color);
        border-radius: 10px;
    }

    /*
    .debug-active {
        border: 1px solid green;
    }

    .debug-inactive {
        border: 1px solid red;
    } */
</style>

<!-- Sidebar -->
<nav class="sidebar shadow-sm" style="padding-bottom: 300px;">
    <div class="sidebar-brand">
        <a href="">
            <img src="{{ asset('storage/' . getLogoPath()) }}" alt="logo" class="logo">
        </a>
    </div>
    <div class="py-2">
        <ul class="nav flex-column">
            @foreach ($menus->where('parent_menu', '1') as $parentMenu)

                {{-- Skip the menu if it's not enabled and not a default --}}
                @if (!$parentMenu->is_default && !isFeatureEnabled($parentMenu->feature_type))
                    <li class="nav-item">
                        <a title="Upgrade Your Plan to Use this feature" data-toggle="tooltip"
                            href="{{ route(getPanelRoutes('planupgrade.index')) }}" class="nav-link">
                            <i class="fas {{ $parentMenu->menu_icon }}"></i>
                            {{ __('menus.' . $parentMenu->menu_name) }}
                            <span class="pro">PRO</span>
                        </a>
                    </li>
                    @continue
                @endif

                @php
                    $childMenus = $menus->where('parent_menu_id', $parentMenu->id);
                    $hasChildPermission = $childMenus->contains(function ($childMenu) use ($parentMenu) {
                        return hasMenuPermission($childMenu->permission_id);
                    });
                    $hasParentPermission = hasMenuPermission($parentMenu->permission_id);

                    // Check if any child menu is active or if the current route contains the parent menu's URL
$isParentMenuActive = isParentMenuActive($currentRoute, $parentMenu->menu_url);

// Debugging
// Log::info('Menu Debug Info', [
//     'parentMenu' => $parentMenu->menu_name,
//     'currentRoute' => $currentRoute,
//     'menuUrl' => $parentMenu->menu_url,
//     'isParentMenuActive' => $isParentMenuActive,
//     'hasChildPermission' => $hasChildPermission,
//     'hasParentPermission' => $hasParentPermission,
                    //                     ]);

                @endphp

                @if ($hasChildPermission || $hasParentPermission)
                    <li class="nav-item debug-{{ $isParentMenuActive ? 'active' : 'inactive' }}">
                        <a href="#menu_item_{{ $parentMenu->id }}"
                            class="nav-link {{ $isParentMenuActive ? 'active' : '' }}" data-bs-toggle="collapse"
                            aria-expanded="{{ $isParentMenuActive ? 'true' : 'false' }}">
                            <i class="fas {{ $parentMenu->menu_icon }}"></i>
                            {{ __('menus.' . $parentMenu->menu_name) }}
                        </a>

                        @if ($childMenus->count())
                            <div class="collapse {{ $isParentMenuActive ? 'show' : '' }}"
                                id="menu_item_{{ $parentMenu->id }}">
                                <ul class="nav flex-column submenu">
                                    @foreach ($childMenus as $childMenu)
                                        @if (hasMenuPermission($childMenu->permission_id))
                                            <li class="nav-item">
                                                <a class="nav-link font-12 {{ $currentRoute === getPanelRoutes($childMenu->menu_url) ? 'active' : '' }}"
                                                    href="{{ route(getPanelRoutes($childMenu->menu_url)) }}">
                                                    <i class="fas fa-angle-double-right"></i>
                                                    {{ __('menus.' . $childMenu->menu_name) }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</nav>
