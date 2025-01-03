@extends('dashboard.settings.settings-layout')



@section('settings_content')
    @php
        // prePrintR($defaultSettings[0]);
        // prePrintR($defaultSettings);
    @endphp

<div class="container-fluid">
    <h3 class="mb-4">Mail Settings</h3>
    <form id="mailSettingsForm" action="{{ route(getPanelRoutes('settings.mail')) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_tenant" value="{{ $is_tenant }}" />

        @foreach ($mail_settings as $key => $item)
            {{-- Show only tenant settings for tenant users --}}
            @if ($is_tenant && str_starts_with($item['name'], 'tenant_'))
                @if ($item['value'] === 'default')
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

                {{-- Show fields based on input type --}}
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

                            <select name="{{ $item['name'] }}" id="{{ $item['key'] }}" class="form-control custom-class" required>
                                @if($item['name'] === 'tenant_mail_encryption')
                                    @foreach ($encryption as $option)
                                        <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                            {{ strtoupper($option) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @break
                @endswitch

            {{-- Show only company settings for company users --}}
            @elseif (!$is_tenant && str_starts_with($item['name'], 'client_'))
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

                            <select name="{{ $item['name'] }}" id="{{ $item['key'] }}" class="form-control custom-class" required>
                                @if($item['name'] === 'client_mail_encryption')
                                    @foreach ($encryption as $option)
                                        <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                            {{ strtoupper($option) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @break
                @endswitch
            @endif
        @endforeach

        <div class="row">
            <div class="col d-flex justify-content-end align-items-center">
                <button type="button" id="testMailConnection" class="btn btn-outline-primary me-2">Test Mail Connection</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script>
        document
            .getElementById("testMailConnection")
            .addEventListener("click", function() {
                const form = document.getElementById("mailSettingsForm");
                const formData = new FormData(form);
                formData.append("_token", "{{ csrf_token() }}"); // Fix append
                formData.append("_method", "POST"); // Fix append

                // Show loader
                const loader = document.getElementById("loadingSpinner");
                loader.style.display = "flex";

                const testConnectionUrl =
                    "{{ route(getPanelRoutes($module . '.test-connection')) }}";

                $.ajax({
                    url: testConnectionUrl,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Hide loader
                        loader.style.display = "none";

                        if (response.success) {
                            // Show success modal
                            const successModal = new bootstrap.Modal(
                                document.getElementById("successModal")
                            );
                            $("#successModal .modal-body").text(
                                "Mail connection successful! Your SMTP settings are working."
                            );
                            successModal.show();
                        } else {
                            // Show alert modal
                            const alertModal = new bootstrap.Modal(
                                document.getElementById("alertModal")
                            );
                            $("#alertModal .modal-body").text(
                                "Mail connection failed: " + response.message
                            );
                            alertModal.show();
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide loader
                        loader.style.display = "none";

                        console.error("Error: ", error);

                        alert(
                            "An error occurred while testing the mail connection. Please check your settings and try again."
                        );
                    },
                });
            });
    </script>
@endpush
