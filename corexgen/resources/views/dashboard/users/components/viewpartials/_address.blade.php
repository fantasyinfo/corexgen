<div class="mt-4">

    <h6 class="detail-label">Address</h6>
    <p class="lead-details">
        {{ $user?->addresses?->street_address }},
        {{ $user?->addresses?->city?->city_name }},
        {{ $user?->addresses?->country?->name }},
        {{ $user?->addresses?->postal_code }}
    </p>
</div>