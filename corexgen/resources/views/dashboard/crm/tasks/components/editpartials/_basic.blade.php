<div class="col-md-6">
    <h6 class="detail-label">Basic Information (Edit)</h6>

    <div class="detail-group">
        <x-form-components.input-label for="clientType" required>
            {{ __('leads.Type') }}
        </x-form-components.input-label>
        <select class="form-select" name="type" id="clientType" required>
            <option value="Individual" {{ $lead->type == 'Company' ? 'selected' : '' }}>Individual</option>
            <option value="Company" {{ $lead->type == 'Company' ? 'selected' : '' }}>
                Company</option>
        </select>
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="title" required>
            {{ __('leads.Title') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="title" id="title"
            placeholder="{{ __('New Development Project Lead') }}" value="{{ old('title', $lead->title) }}" required />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="companyName" required>
            {{ __('leads.Company Name') }}
        </x-form-components.input-label>

        <x-form-components.input-group type="text" name="company_name" id="companyName"
            placeholder="{{ __('Abc Pvt Ltd') }}" value="{{ old('company_name', $lead->company_name) }}" />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="firstName" required>
            {{ __('leads.First Name') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="first_name" id="firstName"
            placeholder="{{ __('First Name') }}" value="{{ old('first_name', $lead->first_name) }}" required />
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="lastName" required>
            {{ __('leads.Last Name') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="last_name" id="lastName"
            placeholder="{{ __('Last Name') }}" value="{{ old('last_name', $lead->last_name) }}" required />
    </div>



    <div class="detail-group">
        <x-form-components.input-label for="emails">
            {{ __('leads.Email') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="email" name="email" id="email"
            placeholder="{{ __('Email Address') }}" value="{{ old('email', $lead->email) }}" required />
    </div>


    <div class="detail-group">
        <x-form-components.input-label for="phones">
            {{ __('leads.Phone') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="tel" name="phone" id="phone"
            placeholder="{{ __('Phone Number') }}" value="{{ old('phone', $lead->phone) }}" required />
    </div>

</div>
