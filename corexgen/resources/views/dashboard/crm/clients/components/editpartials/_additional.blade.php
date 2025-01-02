<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>

    <div class="detail-group">
        <x-form-components.input-label for="group_id">
            {{ __('clients.Category') }}
        </x-form-components.input-label>
        <select class="form-select" name="cgt_id" id="cgt_id">
            @foreach ($categories as $lg)
                <option value="{{ $lg->id }}" {{ old('cgt_id', $client->cgt_id) == $lg->id ? 'selected' : '' }}>
                    {{ $lg->name }}</option>
            @endforeach
        </select>
    </div>

</div>
