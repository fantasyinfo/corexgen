@extends('dashboard.settings.settings-layout')

@section('settings_content')
    <div class="container-fluid">
        <h3 class="mb-4">Theme Customize</h3>
        <form id="themeSettingsForm" action="{{ route(getPanelRoutes('settings.theme')) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_tenant" value="{{ $is_tenant }}" />
            @php
                // prePrintR($theme_settings);
            @endphp
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
            <div class="row">
                <div class="col d-flex justify-content-end align-items-center">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
@endsection
