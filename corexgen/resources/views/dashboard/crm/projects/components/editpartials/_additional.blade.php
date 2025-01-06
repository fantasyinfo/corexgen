<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>
    <div class="detail-group">
        <x-form-components.input-label for="source_id">
            {{ __('leads.Sources') }}
        </x-form-components.input-label>
        <select class="form-select" name="source_id" id="source_id">
            @foreach ($leadsSources as $ls)
                <option value="{{ $ls->id }}"
                    {{ old('source_id', $lead->source_id) == $ls->id ? 'selected' : '' }}>
                    <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="group_id">
            {{ __('leads.Groups') }}
        </x-form-components.input-label>
        <select class="form-select" name="group_id" id="group_id">
            @foreach ($leadsGroups as $lg)
                <option value="{{ $lg->id }}" {{ old('group_id', $lead->group_id) == $lg->id ? 'selected' : '' }}>
                    {{ $lg->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="priority" required>
            {{ __('leads.Priority') }}
        </x-form-components.input-label>
        <select class="form-select" name="priority" id="priority" required>
            @foreach (['Low', 'Medium', 'High'] as $pri)
                <option value="{{ $pri }}" {{ old('priority', $lead->priority) == $pri ? 'selected' : '' }}>
                    {{ $pri }}</option>
            @endforeach
        </select>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="status_id" required>
            {{ __('leads.Stage') }}
        </x-form-components.input-label>
        <select class="form-select" name="status_id" id="status_id" required>
            @foreach ($leadsStatus as $lst)
                <option value="{{ $lst->id }}"
                    {{ old('status_id', $lead->status_id) == $lst->id ? 'selected' : '' }}>
                    <i class="fas fa-dot-circle"></i> {{ $lst->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="is_converted">
            {{ __('leads.Is Captured') }}
        </x-form-components.input-label>
        <input type="checkbox" class="form-check-input" name="is_converted" id="isRequired_0"
            {{ old('is_converted', $lead->is_converted) == 'on' ? 'checked' : '' }}>
        <label class="form-check-label" for="is_converted">
            {{ __('if checked, a client account will also created.') }}
        </label>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="preferred_contact_method" required>
            {{ __('leads.Prefferd Contact') }}
        </x-form-components.input-label>
        <select class="form-select" name="preferred_contact_method" id="preferred_contact_method" required>
            @foreach (['Email', 'Phone', 'In-Person'] as $pcm)
                <option value="{{ $pcm }}"
                    {{ old('preferred_contact_method', $lead->preferred_contact_method) == $pcm ? 'selected' : '' }}>
                    {{ $pcm }}</option>
            @endforeach
        </select>
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="assign_to[]">
            {{ __('leads.Assign To') }}
        </x-form-components.input-label>
        <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates" :name="'assign_to'" :multiple="true"
            :selected="$lead->assignees->pluck('id')->toArray()" />
    </div>
</div>
