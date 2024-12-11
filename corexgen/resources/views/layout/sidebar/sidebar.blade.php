@php
    $menus = getCRMMenus();
    $currentRoute = Route::currentRouteName();

    //prePrintR( Auth::user());

@endphp

<!-- Sidebar -->
<nav class="sidebar shadow-sm">
    <div class="sidebar-brand">
        <a href="">
            <img src="{{ asset('./img/logo.png') }}" alt="logo" class="logo">
        </a>
    </div>
    <div class="py-2">
        <ul class="nav flex-column">
            @foreach ($menus->where('parent_menu', '1') as $parentMenu)
                @php
                    $childMenus = $menus->where('parent_menu_id', $parentMenu->id);

                    $hasChildPermission = $childMenus->contains(function ($childMenu) {
                        return hasMenuPermission($childMenu->permission_id);
                    });
                    $hasParentPermission = hasMenuPermission($parentMenu->permission_id);

                    // Check if any child menu is active
                    $hasActiveChild = $childMenus->contains(function ($childMenu) use ($currentRoute) {
                        return getPanelRoutes($childMenu->menu_url) === $currentRoute;
                    });
                @endphp

                @if ($hasChildPermission || $hasParentPermission)
                    <li class="nav-item">


                        @if (!$parentMenu->is_default)
                            @if (!isFeatureEnable($parentMenu->feature_type))
                                <a title="Upgrade Your Plan to Use this feature" data-toggle="tooltip"
                                    href="{{ route(getPanelRoutes('planupgrade.index')) }}"
                                    class="nav-link ">
                                    <i class="fas {{ $parentMenu->menu_icon }}"></i> {{ $parentMenu->menu_name }}
                                </a>
                                @continue
                            @endif
                        @endif

                        <a href="#menu_item_{{ $parentMenu->id }}"
                            class="nav-link {{ $hasActiveChild ? 'active' : '' }}" data-bs-toggle="collapse"
                            aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}">
                            <i class="fas {{ $parentMenu->menu_icon }}"></i> {{ $parentMenu->menu_name }}
                        </a>


                        @if ($childMenus->count())
                            <div class="collapse {{ $hasActiveChild ? 'show' : '' }}"
                                id="menu_item_{{ $parentMenu->id }}">
                                <ul class="nav flex-column submenu">
                                    @foreach ($childMenus as $childMenu)
                                        @if (hasMenuPermission($childMenu->permission_id))
                                            <li class="nav-item ">
                                                <a class="nav-link  font-12 {{ $currentRoute === getPanelRoutes($childMenu->menu_url) ? 'active' : '' }}"
                                                    href="{{ route(getPanelRoutes($childMenu->menu_url)) }}">
                                                    <i class="fas fa-angle-double-right"></i>
                                                    {{ $childMenu->menu_name }}
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
