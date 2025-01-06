<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>
    <div class="detail-group">
        <label>Assigned To</label>
        <p>
            @foreach ($project->assignees as $user)
                <a href="{{ route(getPanelRoutes('users.view'), ['id' => $user->id]) }}">
                    <x-form-components.profile-avatar :hw="40" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" :title="$user->name" />
                </a>
            @endforeach
        </p>
    </div>
</div>
