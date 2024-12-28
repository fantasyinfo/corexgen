@if (isset($permissions['CHANGE_STATUS']) && hasPermission(strtoupper($module) . '.' . $permissions['CHANGE_STATUS']['KEY']))
    <div class="mx-1 dropdown">
        <a href="#" 
        class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle p-0"
            id="statusDropDown" 
            data-bs-toggle="dropdown" 
            aria-expanded="true"
            data-toggle="tooltip"
            title="Change Status"
            >

            <span class="badge bg-{{ $status['bt_class'][$status['current_status']] }}">
                {{ ucfirst($status['current_status']) }}
            </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="statusDropDown"
            style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate3d(-105px, 26px, 0px);"
            data-popper-placement="bottom-end">
            @foreach ($status['available_status'] as $availableStatus)
                <li>
                    <a class="dropdown-item"
                        href="{{ route($tenantRoute . $module . '.changeStatus', ['id' => $id, 'status' => $availableStatus]) }}">
                        <span class="status-circle bg-{{ $status['bt_class'][$availableStatus] }} me-2"></span>
                        {{ $availableStatus }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <span class="badge bg-{{ $status['bt_class'][$status['current_status']] }}">
        {{ ucfirst($status['current_status']) }}
    </span>
@endif
