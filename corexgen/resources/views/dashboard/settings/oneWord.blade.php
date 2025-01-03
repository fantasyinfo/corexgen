@extends('dashboard.settings.settings-layout')

@section('settings_content')
    <div class="container-fluid">
        <h3 class="mb-4">One Word Settings</h3>
        <form action="{{ route(getPanelRoutes('settings.oneWord')) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

  
            @foreach ($oneWordSettings as $key => $item)
              
                   

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
            
            @endforeach

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
@endsection