<div class="col-md-6">
    <h6 class="detail-label">Basic Information (Edit)</h6>
    <div class="detail-group">
        <x-form-components.input-label for="role_id" class="custom-class" required>
            {{ __('users.Select Role') }}
        </x-form-components.input-label>
        <select class="form-control searchSelectBox @error('role_id') is-invalid @enderror" name="role_id" id="role_id">
            @if ($roles && $roles->isNotEmpty())
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->role_name }}
                    </option>
                @endforeach
            @else
                <option disabled>No roles available</option>
            @endif
        </select>
        <div class="invalid-feedback" id="role_idError">
            @error('role_id')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="nameName" class="custom-class" required>
            {{ __('users.Full Name') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" class="custom-class" id="nameName" name="name"
            placeholder="{{ __('John Doe') }}" value="{{ old('name', $user->name) }}" required />
    </div>
    <div class="detail-group">
        <label>Company Name</label>
        <p>{{ $user?->company?->company_name }}</p>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="emailName" class="custom-class" required>
            {{ __('users.Email') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="email" class="custom-class" id="emailName" name="email"
            placeholder="{{ __('john@email.com') }}" value="{{ old('email', $user->email) }}" required disabled />
    </div>

</div>
