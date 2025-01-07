@php
    $menus = getCRMMenus();
    $currentRoute = Route::currentRouteName();

    // prePrintR( $menus->toArray());


@endphp

<style>
    .pro{
        border:1px dotted red;
        padding: 3px 10px;
        color: var(--primary-color);
        border-radius: 10px;
    }
</style>
<!-- Sidebar -->
<nav class="sidebar shadow-sm"  style="padding-bottom:300px;">
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
                        <i class="fas {{ $parentMenu->menu_icon }}"></i> {{ $parentMenu->menu_name }} <span class='pro'>PRO</span>
                    </a>
                </li>
                @continue
            @endif

            @php
                $childMenus = $menus->where('parent_menu_id', $parentMenu->id);
                $hasChildPermission = $childMenus->contains(function ($childMenu) use ($parentMenu){
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
                                        <li class="nav-item">
                                            <a class="nav-link font-12 {{ $currentRoute === getPanelRoutes($childMenu->menu_url) ? 'active' : '' }}"
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
