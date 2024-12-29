<div class="mt-4">
    <h6 class="detail-label">Address</h6>
    <p class="lead-details">
        {{ $lead?->address?->street_address }},
        {{ $lead?->address?->city?->name }},
        {{ $lead?->address?->country?->name }},
        {{ $lead?->address?->postal_code }}
    </p>
</div>