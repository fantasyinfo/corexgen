<div class="mt-4">
    <h6 class="detail-label">Address</h6>
    <p class="lead-details">
        {{ $company?->addresses?->street_address }},
        {{ $company?->addresses?->city?->name }},
        {{ $company?->addresses?->country?->name }},
        {{ $company?->addresses?->postal_code }}
    </p>
</div>