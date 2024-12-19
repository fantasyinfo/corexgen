<label 
    for="{{ @$for }}" 
    class="mb-2 fw-semibold {{ $attributes->get('class') }}">
    {{ $slot }}
    @if ($required ?? false)
        <span class="text-danger">*</span>
    @endif
</label>

