@php
    $menus = getCRMMenus();
    $currentRoute = Route::currentRouteName();
@endphp
   
<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <a href="">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Google_2015_logo.svg/800px-Google_2015_logo.svg.png" 
                 alt="logo" 
                 class="logo">
        </a>
    </div>
    <div class="py-2">
        <ul class="nav flex-column">
            @foreach($menus->where('parent_menu', '1') as $parentMenu)
                @php
                    $childMenus = $menus->where('parent_menu_id', $parentMenu->id);
                    $hasChildPermission = $childMenus->contains(function ($childMenu) {
                        return hasMenuPermission($childMenu->permission_id);
                    });
                    $hasParentPermission = hasMenuPermission($parentMenu->permission_id);
                    
                    // Check if any child menu is active
                    $hasActiveChild = $childMenus->contains(function ($childMenu) use ($currentRoute) {
                        return $childMenu->menu_url === $currentRoute;
                    });
                @endphp

                @if($hasChildPermission || $hasParentPermission)
                <li class="nav-item">
                    <a href="#menu_item_{{ $parentMenu->id }}" 
                       class="nav-link {{ $hasActiveChild ? 'active' : '' }}" 
                       data-bs-toggle="collapse"
                       aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}">
                       <i class="fas {{$parentMenu->menu_icon}}"></i> {{ $parentMenu->menu_name }} 
                    </a>

                    @if($childMenus->count())
                    <div class="collapse {{ $hasActiveChild ? 'show' : '' }}" 
                         id="menu_item_{{ $parentMenu->id }}">
                        <ul class="nav flex-column submenu">
                            @foreach($childMenus as $childMenu)
                                @if(hasMenuPermission($childMenu->permission_id))
                                    <li class="nav-item ">
                                        <a class="nav-link  font-12 {{ $currentRoute === $childMenu->menu_url ? 'active' : '' }}" 
                                           href="{{ route($childMenu->menu_url) }}"> 
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