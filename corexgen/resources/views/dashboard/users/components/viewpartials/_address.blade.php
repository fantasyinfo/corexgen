<div class="mt-4">
    <h6 class="detail-label">Address</h6>
    <p class="lead-details">
        {{ $user?->address?->street_address }},
        {{ $user?->address?->city?->name }},
        {{ $user?->address?->country?->name }},
        {{ $user?->address?->postal_code }}
    </p>
</div>