<div class="col-md-6">
    <h6 class="detail-label">Basic Information (Edit)</h6>

    <div class="detail-group">
        <x-form-components.input-label for="title" required>
            {{ __('tasks.Title') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="text" name="title" id="title" placeholder="{{ __('Enter Title') }}"
            value="{{ old('title', $task->title) }}" required class="custom-class" />
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="hourly_rate">
            {{ __('tasks.Hourly Rate') }}
        </x-form-components.input-label>
        <x-form-components.input-group-prepend-append type="number" class="custom-class" id="hourly_rate"
            step="0.001" prepend="{{ getSettingValue('Currency Symbol') }}"
            append="{{ getSettingValue('Currency Code') }}" name="hourly_rate" placeholder="{{ __('99999') }}"
            value="{{ old('hourly_rate', $task->hourly_rate) }}" />
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="start_date">
            {{ __('tasks.Start Date') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="date" placeholder="Select Date" name="start_date" id="start_date"
            value="{{ old('start_date', $task->start_date) }}" class="custom-class" />
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="due_date">
            {{ __('tasks.Due Date') }}
        </x-form-components.input-label>
        <x-form-components.input-group type="date" placeholder="Select Date" name="due_date" id="due_date"
            value="{{ old('due_date', $task->due_date) }}" class="custom-class" />
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="related_to" required>
            {{ __('tasks.Related To') }}
        </x-form-components.input-label>
        <select class="form-select" name="related_to" id="related_to" required>
            @foreach (TASKS_RELATED_TO['STATUS'] as $key => $pri)
                <option value="{{ $key }}"
                    {{ old('related_to', $task->related_to) == $key ? 'selected' : '' }}>
                    {{ $pri }}</option>
            @endforeach
        </select>
        @error('related_to')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="project_id">
            {{ __('tasks.Project') }}
        </x-form-components.input-label>
        <select class="form-select searchSelectBox" name="project_id" id="project_id">
            @foreach ($projects as $pro)
                <option value="{{ $pro->id }}"
                    {{ old('project_id', $task->project_id) == $pro->id ? 'selected' : '' }}>
                    {{ $pro->title }}</option>
            @endforeach
        </select>
        @error('project_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

</div>
