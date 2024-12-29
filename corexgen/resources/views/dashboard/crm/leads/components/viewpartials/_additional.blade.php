<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>
    <div class="detail-group">
        <label>Source</label>
        <p>{{ $lead->source->name ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Group</label>
        <p>{{ $lead->group->name ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Priority</label>
        <p>{{ $lead->priority ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Stage</label>
        <p>{{ $lead->stage->name ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Is converted</label>
        <p>{{ $lead->is_converted == '1' ? 'Yes' : 'No' }}</p>
    </div>
    <div class="detail-group">
        <label>Preferred Contact</label>
        <p>{{ $lead->preferred_contact_method }}</p>
    </div>
    <div class="detail-group">
        <label>Assigned To</label>
        <p>
            @foreach ($lead->assignees as $user)
                <a
                    href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                    <x-form-components.profile-avatar :hw="40"
                        :url="asset(
                            'storage/' .
                                ($user->profile_photo_path ??
                                    'avatars/default.webp'),
                        )" :title="$user->name" />
                </a>
            @endforeach
        </p>
    </div>
</div>