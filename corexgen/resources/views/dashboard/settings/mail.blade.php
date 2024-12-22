@extends('dashboard.settings.settings-layout')



@section('settings_content')
    @php
        // prePrintR($defaultSettings[0]);
        // prePrintR($defaultSettings);
    @endphp

    <div class="container-fluid">
        <h3 class="mb-4">Mail Settings</h3>
        <form action="{{ route(getPanelRoutes('settings.mail')) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_tenant" value="{{ $is_tenant }}" />

            @foreach ($mail_settings as $key => $item)
                @if ($item['value'] === 'default' && $is_tenant)
                    @switch($item['name'])
                        @case('tenant_mail_provider')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details'])) {
                                    $item['value'] = 'smtp';
                                }
                            @endphp
                        @break

                        @case('tenant_mail_host')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['smtp_host'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['smtp_host'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_port')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['smtp_port'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['smtp_port'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_username')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['smtp_username'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['smtp_username'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_password')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['smtp_password'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['smtp_password'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_encryption')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['smtp_encryption'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['smtp_encryption'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_from_address')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['mail_from_address'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['mail_from_address'];
                                }
                            @endphp
                        @break

                        @case('tenant_mail_from_name')
                            @php
                                if (isset($defaultSettings) && isset($defaultSettings['smtp_details']['mail_from_name'])) {
                                    $item['value'] = $defaultSettings['smtp_details']['mail_from_name'];
                                }
                            @endphp
                        @break
                    @endswitch
                @endif
                @switch($item['input_type'])
                    @case('text')
                    @case('number')

                    @case('email')
                    @case('password')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <x-form-components.input-group type="{{ $item['input_type'] }}" class="custom-class"
                                id="{{ $item['key'] }}" name="{{ $item['name'] }}" placeholder="{{ $item['placeholder'] }}"
                                value="{{ old($item['name'], $item['value']) }}" required />
                        </div>
                    @break

                    @case('dropdown')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <select name="{{ $item['name'] }}" id="{{ $item['key'] }}"
                                class="searchSelectBox form-control custom-class" required>
                                @if ($is_tenant)
                                    @switch($item['name'])
                                        @case('tenant_mail_encryption')
                                            @foreach ($encryption as $option)
                                                <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        @break
                                    @endswitch
                                @else
                                    @switch($item['name'])
                                        @case('client_mail_encryption')
                                            @foreach ($encryption as $option)
                                                <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        @break
                                    @endswitch
                                @endif

                            </select>
                        </div>
                    @break

                @endswitch
            @endforeach

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection
