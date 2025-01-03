@extends('layout.app')

@push('style')
    <style>
        .product-header {
            /* background: #f8f9fa; */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
        }

        .info-card {
            transition: transform 0.2s;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }
    </style>
@endpush

@section('content')
    @php
        // prePrintR($product->toArray());
        // die();
    @endphp
    <div class="container-fluid ">
        <div class="row">
            <!-- Product Header Section -->
            <div class="col-12 mb-4">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-2 text-muted ">{{ $product?->type }}</h6>
                            <h1 class="h3 mb-2">{{ $product?->title }}</h1>
                            <div class="d-flex align-items-center gap-3">
                                <span
                                    class="badge bg-{{ $product?->status === 'ACTIVE' ? 'success' : 'danger' }} status-badge">
                                    <i class="fas fa-circle me-1"></i>
                                    {{ $product?->status }}
                                </span>
                                <span class="text-muted">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ $product?->category?->name }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <h2 class="h3 mb-1">
                                <span class="text-success">{{ getSettingValue('Currency Symbol') }}</span>
                                {{ number_format($product?->rate, 2) }} <span
                                    class="text-muted text-sm">{{ getSettingValue('Currency Code') }}</span> /
                                {{ $product?->unit }} {{$product?->type == 'Service' ? 'Hr' : 'Qty'}}
                            </h2>
                            <span class="badge bg-primary">

                                {{ $product?->tax?->name }} Tax
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Section -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Product Details
                        </h4>
                        <div class="row g-4">

                            <div class="info-card  p-3 rounded">
                                <small class="text-muted d-block mb-1">Description</small>
                                <p class="mb-0">{{ $product?->description }}</p>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Additional Information
                        </h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-card  p-3 rounded">
                                    <small class="text-muted d-block mb-1">Created Information</small>
                                    <p class="mb-1">
                                        <i class="fas fa-user me-2"></i>
                                        User ID: {{ $product?->createdBy?->name }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ \Carbon\Carbon::parse($product?->created_at)->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card  p-3 rounded">
                                    <small class="text-muted d-block mb-1">Last Updated</small>
                                    <p class="mb-1">
                                        <i class="fas fa-user-edit me-2"></i>
                                        User ID: {{ $product?->updatedBy?->name }}
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-clock me-2"></i>
                                        {{ \Carbon\Carbon::parse($product?->updated_at)->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="fas fa-bookmark me-2"></i>
                            Quick Information
                        </h4>
                        <ul class="list-unstyled mb-0">

                            <li class="mb-3">
                                <small class="text-muted d-block">Category</small>
                                <span class="badge bg-{{ $product?->category?->color }}">
                                    {{ $product?->category?->name }}
                                </span>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Tax Rate</small>
                                <span class="badge bg-{{ $product?->tax?->color }}">
                                    {{ $product?->tax?->name }}
                                </span>
                            </li>
                            <li>
                                <small class="text-muted d-block">Slug</small>
                                <code>{{ $product?->slug }}</code>
                            </li>
                        </ul>
                    </div>
                </div>

                @if (isset($customFields) && $customFields->isNotEmpty())
                    <div class="card  shadow-sm mb-4">

                        <div class="card-body  gap-2">
                            <h4 class="card-title mb-4">
                                <i class="fas fa-plus me-2"></i>
                                Custom Fields
                            </h4>
                            <ul class="list-unstyled mb-0">
                                @foreach ($customFields as $cf)
                                    @php
                                        // Find the existing value for this custom field
                                        $existingValue = $cfOldValues->firstWhere('definition_id', $cf['id']);
                                        $fieldValue = $existingValue
                                            ? $existingValue['field_value']
                                            : old('custom_fields.' . $cf['id'], '');
                                    @endphp
                                    <li>
                                        <small class="text-muted d-block">{{ ucfirst($cf['field_label']) }}</small>
                                        <code>{{ trim($fieldValue) != '' || null ? $fieldValue : 'NA' }}</code>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif


            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add any necessary JavaScript functionality here
        });
    </script>
@endpush
