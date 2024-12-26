@props(['type' => 'primary', 'icon' => 'fa-info-circle', 'title' => 'General'])

<div class="alert alert-{{ $type }}">
    <div class="d-flex align-items-center">
        <!-- Icon on the left -->
        <i class="fas {{ $icon }} me-3 text-{{ $type }}" style="font-size: 24px;"></i>

        <!-- Text on the right -->
        <div class="flex-grow-1">
            <h5 class="text-{{ $type }} mb-1">{{ $title }}</h5>
            <p class="font-12  mb-0">Please add / update correct details</p>
        </div>
    </div>
</div>
