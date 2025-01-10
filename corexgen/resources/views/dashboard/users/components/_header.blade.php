<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <x-form-components.profile-avatar :hw="80" :url="asset('storage/' . ($user->profile_photo_path ?? 'avatars/default.webp'))" />
            <div>
                <h1 class="mb-1"> {{ $user?->name }} </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $user?->role?->role_name }}
                    </span>
                </div>
            </div>
        </div>
    </div>
 
</div>
