<div class="mt-4">
    <h6 class="detail-label">Address</h6>
    <p class="lead-details">
    <p class="my-1">
        <x-form-components.textarea-group name="address.street_address" id="compnayAddressStreet"
            placeholder="Enter Registered Street Address" class="custom-class"
            value="{{ old('address.street_address', $lead?->address?->street_address) }}" />
    </p>
    <h6 class="detail-label">Country</h6>
    <p class="my-1">
        <select class="form-control searchSelectBox  @error('address.country_id') is-invalid @enderror"
            name="address.country_id" id="country_id">

            @if ($countries)
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}"
                        {{ old('address.country_id', $lead?->address?->country_id) == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            @else
                <option disabled>No country available</option>
            @endif
        </select>
    </p>
    <h6 class="detail-label mt-4">City</h6>
    <p class="my-1">
        <x-form-components.input-group type="text" name="address.city_name" id="compnayAddressCity"
            placeholder="{{ __('Enter City') }}" value="{{ old('address.city_name', $lead?->address?->city?->name) }}"
            class="custom-class" />
    </p>
    <h6 class="detail-label">Pincode</h6>
    <p class="my-1">
        <x-form-components.input-group type="text" name="address.pincode" id="compnayAddressPincode"
            placeholder="{{ __('Enter Pincode') }}" value="{{ old('address.pincode', $lead?->address?->postal_code) }}"
            class="custom-class" />
    </p>
    </p>
</div>
