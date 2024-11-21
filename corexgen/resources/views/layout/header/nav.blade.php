@php
    $menus = getCRMMenus();
@endphp

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="index.html" class="b-brand">
                <!-- ========   change your logo hear   ============ -->
                <img src="{{asset('assets/images/logo-full.png')}}" alt="" class="logo logo-lg" />
                <img src="{{asset('assets/images/logo-abbr.png')}}" alt="" class="logo logo-sm" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
            
                @foreach($menus->where('parent_menu', '1') as $parentMenu)
                    @php
                        $childMenus = $menus->where('parent_menu_id', $parentMenu->id);
                        $hasChildPermission = $childMenus->contains(function ($childMenu) {
                            return hasMenuPermission($childMenu->permission_id);
                        });
                        $hasParentPermission = hasMenuPermission($parentMenu->permission_id);
                    @endphp
        
                    @if($hasChildPermission || $hasParentPermission)
                    <li>
                        <a href="javascript:void(0);" class="nxl-link">
                            <span class="nxl-micon"><i class="{{$parentMenu->menu_icon}}"></i></span>
                            <span class="nxl-mtext">{{ $parentMenu->menu_name }}</span>
                            <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                        </a>
        
                        @if($childMenus->count())
                            <ul class="nxl-submenu">
                                @foreach($childMenus as $childMenu)
                                    @if(hasMenuPermission($childMenu->permission_id))
                                        <li class="nxl-item">
                                            <a class="nxl-link" href="{{ route($childMenu->menu_url) }}"> 
                                                <i class="{{$childMenu->menu_icon}}"></i>{{ $childMenu->menu_name }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
</nav>