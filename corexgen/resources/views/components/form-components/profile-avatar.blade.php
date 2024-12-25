@props(['url' => 'storage/avatars/default.webp', 'hw' => '60'])

<img src="{{ $url }}" class="rounded-circle border border-3" id="avatarPreview"
    style="width: {{ $hw }}px; height: {{ $hw }}px; object-fit: cover; border-color: var(--primary-color)">
