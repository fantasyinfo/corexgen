@extends('dashboard.settings.settings-layout')



@section('settings_content')
    {{-- @php
        prePrintR($general_settings);
    @endphp  --}}

    <div class="container-fluid">
        <h3 class="mb-4">General Settings</h3>
        <form action="{{ route(getPanelRoutes('settings.general')) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @foreach ($general_settings as $key => $item)
                @switch($item['input_type'])
                    @case('text')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <x-form-components.input-group type="{{ $item['input_type'] }}" class="custom-class"
                                id="{{ $item['key'] }}" name="{{ $item['name'] }}" placeholder="{{ $item['placeholder'] }}"
                                value="{{ $item['value'] }}" required />
                        </div>
                    @break

                    @case('dropdown')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <select name="{{ $item['name'] }}" id="{{ $item['key'] }}" class="form-control custom-class"
                                required>
                                @switch($item['name'])
                                    @case('tenant_company_date_format')
                                        @php $dateTimeFormats = [
                                                                                            'Y-m-d H:i:s' => 'Y-m-d H:i:s | 2024-12-14 12:34:56',
                                                                                            'd-m-Y H:i' => 'd-m-Y H:i | 14-12-2024 12:34',
                                                                                            'm/d/Y g:i A' => 'm/d/Y g:i A | 12/14/2024 12:34 PM',
                                                                                            'D, M j, Y' => 'D, M j, Y | Sat, Dec 14, 2024',
                                                                                            'l, F j, Y' => 'l, F j, Y | Saturday, December 14, 2024',
                                                                                            'c' => 'c | ISO 8601: 2024-12-14T12:34:56+00:00',
                                                                                            'r' => 'r | RFC 2822: Sat, 14 Dec 2024 12:34:56 +0000',
                                                                                        ];
                                                                                @endphp ?>
                                        @foreach ($dateTimeFormats as $key => $option)
                                            <option value="{{ $key }}" {{ $key == $item['value'] ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    @break

                                    @case('tenant_company_time_zone')
                                        @foreach (DateTimeZone::listIdentifiers() as $option)
                                            <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    @break
                                @endswitch

                            </select>
                        </div>
                    @break

                    @case('image')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <div class="custom-file">
                                <input type="file" class="custom-file-input form-control" id="{{ $item['key'] }}"
                                    name="{{ $item['name'] }}" accept="image/*">

                            </div>

                            @if ($item['media_id'] == null)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $item['value']) }}" alt="{{ $item['key'] }}" class="img-thumbnail"
                                        style="max-width: 200px;">
                                </div>
                            @else 
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $item['media']['file_path']) }}" alt="{{ $item['key'] }}" class="img-thumbnail"
                                    style="max-width: 200px;">
                            </div>
                            @endif
                        </div>
                    @break

                @endswitch
            @endforeach

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection
