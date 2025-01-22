<div class="col-md-6">
    <h6 class="detail-label">Basic Information (Edit)</h6>

    <div class="detail-group">
        <x-form-components.input-label for="clientType" required>
            {{ __('clients.Type') }}
        </x-form-components.input-label>
        <select class="form-select" name="type" id="clientType" required>
            <option value="Individual" {{ $client->type == 'Company' ? 'selected' : '' }}>Individual</option>
            <option value="Company" {{ $client->type == 'Company' ? 'selected' : '' }}>
                Company</option>
        </select>
    </div>



    <div class="detail-group">
        <x-form-components.input-label for="companyName" required>
            {{ __('clients.Company Name') }}
        </x-form-components.input-label>

        <x-form-components.input-group type="text" name="company_name" id="companyName"
            placeholder="{{ __('Abc Pvt Ltd') }}" value="{{ old('company_name', $client->company_name) }}" />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="title" >
            {{ __('clients.Title') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="title" id="title"
            placeholder="{{ __('Mr, Miss, Dr, Master...') }}" value="{{ old('title', $client->title) }}"  />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="firstName" required>
            {{ __('clients.First Name') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="first_name" id="firstName"
            placeholder="{{ __('First Name') }}" value="{{ old('first_name', $client->first_name) }}" required />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="lastName" required>
            {{ __('clients.Last Name') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="last_name" id="lastName"
            placeholder="{{ __('Last Name') }}" value="{{ old('last_name', $client->last_name) }}" required />
    </div>



    <div class="detail-group">
        <x-form-components.input-label for="emails">
            {{ __('clients.Email') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="email" name="email[0]" id="email"
            placeholder="{{ __('Email Address') }}" value="{{ old('email[0]', @$client->email[0]) }}" required />
    </div>


    <div class="detail-group">
        <x-form-components.input-label for="phones">
            {{ __('clients.Phone') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="tel" name="phone[0]" id="phone"
            placeholder="{{ __('Phone Number') }}" value="{{ old('phone[0]', @$client->phone[0]) }}" required />
    </div>

</div>
