<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>

    @php
        // prePrintR($task->toArray());
    @endphp
    <div class="detail-group">
        <label>Milestones</label>
       <p>{{$task?->milestone?->name}}</p>
    </div>
    <div class="detail-group">
        <label>Priority</label>
        <p>{{ $task?->priority ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Stage</label>
        <p>{{ $task?->stage?->name ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Assigned To</label>
        <p>
            @foreach ($task?->assignees as $user)
                <a href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                    <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" :title="$user->name" />
                </a>
            @endforeach
        </p>
    </div>
</div>
