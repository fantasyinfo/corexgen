@props(['url' => 'storage/avatars/default.webp', 'hw' => '60', 'id' => 'avt'])

<img src="{{ $url }}" class="rounded-circle border border-3" id="{{$id}}"
    style="width: {{ $hw }}px; height: {{ $hw }}px; object-fit: cover; border-color: var(--primary-color)">
