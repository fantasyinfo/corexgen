@if (hasPermission('ROLE.CHANGE_STATUS'))
    <a data-toggle="tooltip" data-placement="top"
        title="{{ $role->status == CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'] ? 'De Active' : 'Active' }}"
        href="{{ route($tenantRoute . 'role.changeStatus', ['id' => $role->id]) }}">
        <span
            class="badge {{ $role->status == CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'] ? 'bg-success' : 'bg-danger' }}">
            {{ ucfirst($role->status) }}
        </span>
    </a>
@else
    <span
        class="badge {{ $role->status == CRM_STATUS_TYPES['CRM_ROLES']['STATUS']['ACTIVE'] ? 'bg-success' : 'bg-danger' }}">
        {{ ucfirst($role->status) }}
    </span>
@endif
