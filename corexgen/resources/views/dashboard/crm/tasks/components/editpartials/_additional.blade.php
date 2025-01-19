<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>

    <div class="detail-group">
        <x-form-components.input-label for="milestone_id">
            {{ __('tasks.Milestone') }}
        </x-form-components.input-label>
        <select class="form-select searchSelectBox" name="milestone_id" id="milestone_id">
            <option>Select Milestone (optional)</option>
            @foreach ($milestones as $ml)
                <option value="{{ $ml->id }}"
                    {{ old('milestone_id', $task->milestone_id) == $ml->id ? 'selected' : '' }}>
                    {{ $ml->name }}</option>
            @endforeach
        </select>
        @error('milestone_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="detail-group">
        <x-form-components.input-label for="priority" required>
            {{ __('tasks.Priority') }}
        </x-form-components.input-label>
        <select class="form-select" name="priority" id="priority" required>
            @foreach (['Low', 'Medium', 'High', 'Urgent'] as $pri)
                <option value="{{ $pri }}" {{ old('priority', $task->priority) == $pri ? 'selected' : '' }}>
                    {{ $pri }}</option>
            @endforeach
        </select>
        @error('priority')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="status_id" required>
            {{ __('tasks.Status') }}
        </x-form-components.input-label>
        <select class="form-select" name="status_id" id="status_id" required>
            @foreach ($tasksStatus as $ts)
                <option value="{{ $ts->id }}"
                    {{ old('status_id', $task->status_id) == $ts->id ? 'selected' : '' }}>
                    {{ $ts->name }}</option>
            @endforeach
        </select>
        @error('status_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="detail-group">
        <x-form-components.input-label for="assign_to[]">
            {{ __('tasks.Assign To') }}
        </x-form-components.input-label>
        @foreach ($task->assignees as $user)
            <a style="text-decoration: none;" href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" :title="$user->name" />
            </a>
        @endforeach
      
        <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates" :name="'assign_to'" :multiple="true"
            :selected="$task->assignees->pluck('id')->toArray()" />
    </div>
</div>
