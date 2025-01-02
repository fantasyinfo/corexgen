@php
    // prePrintR($client?->addresses?->toArray());
    // die();
@endphp
@if($client?->addresses)
<div class="mt-4">
    <h6 class="detail-label">Addresses</h6>
    <div class="row">
        @foreach ($client->addresses as $address)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title text-primary">Type: <span class="fw-bold">{{ strtoupper($address?->pivot?->type) }}</span></h6>
                    <p class="card-text">
                        {{ $address?->street_address }}<br>
                        {{ $address?->city?->name }}, {{ $address?->country?->name }}<br>
                        {{ $address?->postal_code }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<p class="text-muted">No Address Added for this client.</p>
@endif
