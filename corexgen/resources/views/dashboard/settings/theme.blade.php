@extends('dashboard.settings.settings-layout')

@section('settings_content')
    <div class="container-fluid">
        <h3 class="mb-4">Theme Customize</h3>
        <form id="themeSettingsForm" action="{{ route(getPanelRoutes('settings.theme')) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_tenant" value="{{ $is_tenant }}" />

            @foreach ($theme_settings as $item)
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                        {{ $item['key'] }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="{{ $item['input_type'] }}" class="custom-class"
                        id="{{ $item['key'] }}" name="{{ $item['name'] }}" placeholder="{{ $item['placeholder'] }}"
                        value="{{ old($item['name'], $item['value']) }}" required />
                </div>
            @endforeach
            <div id="showDefaultAlert"></div>
            <div class="row">
                <div class="col d-flex justify-content-end align-items-center gap-2">
                    <button type="button" class="btn btn-secondary" id="resetTheme">Reset to Default</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            const CRM_TENANT_THEME_LIGHT_SETTINGS = @json(CRM_TENANT_THEME_LIGHT_SETTINGS);
            const CRM_TENANT_THEME_DARK_SETTINGS = @json(CRM_TENANT_THEME_DARK_SETTINGS);
            const CRM_COMPANY_THEME_LIGHT_SETTINGS = @json(CRM_COMPANY_THEME_LIGHT_SETTINGS);
            const CRM_COMPANY_THEME_DARK_SETTINGS = @json(CRM_COMPANY_THEME_DARK_SETTINGS);

            console.log('Settings loaded:', {
                tenant_light: CRM_TENANT_THEME_LIGHT_SETTINGS,
                tenant_dark: CRM_TENANT_THEME_DARK_SETTINGS,
                company_light: CRM_COMPANY_THEME_LIGHT_SETTINGS,
                company_dark: CRM_COMPANY_THEME_DARK_SETTINGS
            });

            $(document).ready(function() {
                const isTenant = {{ $is_tenant ? 'true' : 'false' }};
                console.log('Is Tenant:', isTenant);

                $('#resetTheme').on('click', function() {
                    if (!confirm('Are you sure you want to reset theme colors to default?')) {
                        return;
                    }

                    $('input[type="color"]').each(function() {
                        const input = $(this);
                        const inputName = input.attr('name');
                        let settings;
                        let defaultSetting;

                        console.log('Processing input:', {
                            name: inputName,
                            currentValue: input.val()
                        });

                        if (isTenant) {
                            // For tenant users
                            settings = inputName.endsWith('-d') ?
                                CRM_TENANT_THEME_DARK_SETTINGS :
                                CRM_TENANT_THEME_LIGHT_SETTINGS;
                        } else {
                            // For company users
                            settings = inputName.endsWith('-d-company') ?
                                CRM_COMPANY_THEME_DARK_SETTINGS :
                                CRM_COMPANY_THEME_LIGHT_SETTINGS;
                        }

                        // Find the setting where the 'name' field matches our input name
                        for (const key in settings) {
                            if (settings[key].name === inputName) {
                                defaultSetting = settings[key];
                                break;
                            }
                        }

                        console.log('Found setting:', defaultSetting);

                        if (defaultSetting && defaultSetting.value) {
                            console.log('Setting new value:', defaultSetting.value);
                            input.val(defaultSetting.value);
                            input.trigger('change');
                        } else {
                            console.warn('No matching setting found for:', inputName);
                        }
                    });

                    const alertDiv = $('<div>')
                        .addClass('alert alert-success alert-dismissible fade show mt-3')
                        .text('Theme colors have been reset to default values. Click Save Changes to apply.')
                        .append('<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');

                    $('#showDefaultAlert').html(alertDiv);

                    setTimeout(() => {
                        alertDiv.alert('close');
                    }, 5000);
                });

                $('input[type="color"]').on('change', function() {
                    $(this).closest('.row').addClass('bg-light-subtle');
                    setTimeout(() => {
                        $(this).closest('.row').removeClass('bg-light-subtle');
                    }, 300);
                });
            });
        </script>
    @endpush
@endsection
