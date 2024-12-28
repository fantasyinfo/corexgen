@props([
    'url' => 'storage/avatars/default.webp', 
    'hw' => '60', 
    'id' => 'avt',
    'title' => 'profile'
    ])

<img 
src="{{ $url }}" 
title="{{$title}}"
data-toggle="tooltip"
class="rounded-circle border border-3" id="{{$id}}"
style="width: {{ $hw }}px; height: {{ $hw }}px; object-fit: cover; border-color: var(--primary-color)">
