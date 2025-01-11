@extends('dashboard.settings.settings-layout')

@section('settings_content')
    <div class="container-fluid">
        <h3 class="mb-4">General Settings</h3>
        <form action="{{ route(getPanelRoutes('settings.general')) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_tenant" value="{{$is_tenant}}" />
  
            @foreach ($general_settings as $key => $item)
                @if (($is_tenant && $item['is_tenant'] == '1') || (!$is_tenant && $item['is_tenant'] == '0'))
                    @if ($item['value'] === 'default')
                        @switch($item['name'])
                            @case('tenant_company_time_zone')
                                @php
                                    $item['value'] = $defaultSettings->timezone ?? 'default';
                                @endphp 
                            @break
                            @case('tenant_company_currency_symbol')
                                @php
                                    $item['value'] = $defaultSettings->currency_symbol ?? '$';
                                @endphp 
                            @break
                            @case('tenant_company_currency_code')
                                @php
                                    $item['value'] = $defaultSettings->currency_code ?? 'USD';
                                @endphp 
                            @break
                            @case('client_company_time_zone')
                                @php
                                    $item['value'] = $defaultSettings->timezone ?? 'default';
                                @endphp 
                            @break
                            @case('client_company_currency_symbol')
                                @php
                                    $item['value'] = $defaultSettings->currency_symbol ?? '$';
                                @endphp 
                            @break
                            @case('client_company_currency_code')
                                @php
                                    $item['value'] = $defaultSettings->currency_code ?? 'USD';
                                @endphp 
                            @break
                            @case('client_company_name')
                                @php
                                    $item['value'] = $company->name ?? 'default';
                                @endphp 
                            @break
                        @endswitch
                    @endif

                    @switch($item['input_type'])
                        @case('text')
                            <div class="row mb-4 align-items-center">
                                <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                    {{ $item['key'] }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="{{ $item['input_type'] }}" class="custom-class"
                                    id="{{ $item['key'] }}" name="{{ $item['name'] }}" placeholder="{{ $item['placeholder'] }}"
                                    value="{{ old($item['name'], $item['value']) }}" required />
                            </div>
                        @break
                        @case('textarea')
                        <div class="row mb-4 align-items-center">
                            <x-form-components.input-label for="{{ $item['key'] }}" class="custom-class" required>
                                {{ $item['key'] }}
                            </x-form-components.input-label>

                            <x-form-components.textarea-group type="{{ $item['input_type'] }}" class="custom-class"
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
                                    @if($item['name'] == 'tenant_company_date_format' || $item['name'] == 'client_company_date_format')
                                        @foreach ($dateTimeFormats as $key => $option)
                                            <option value="{{ $key }}" {{ $key == $item['value'] ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    @elseif($item['name'] == 'tenant_company_time_zone' || $item['name'] == 'client_company_time_zone')
                                        @foreach (DateTimeZone::listIdentifiers() as $option)
                                            <option value="{{ $option }}" {{ $option == $item['value'] ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    @elseif($item['name'] == 'tenant_company_time_zone' || $item['name'] == 'client_company_address_country_id')
                                        @foreach ($countries as $option)
                                        <option value="{{ $option->id }}" {{ $option->id == $item['value'] ? 'selected' : '' }}>
                                            {{ $option->name }}
                                        </option>
                                        @endforeach
                                    @endif
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
                                        <img src="{{ asset('storage/' . $item['value']) }}" alt="{{ $item['key'] }}"
                                            class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $item['media']['file_path']) }}" alt="{{ $item['key'] }}"
                                            class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                @endif
                            </div>
                        @break
                    @endswitch
                @endif
            @endforeach

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection