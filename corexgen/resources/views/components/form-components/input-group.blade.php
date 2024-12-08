<div class="input-group">
    <input type="{{ $attributes->get('type') }}"
        class="form-control {{ $attributes->get('class') }} @error($attributes->get('name')) is-invalid @enderror"
        id="{{ $attributes->get('id') ?? $attributes->get('name') }}" name="{{ $attributes->get('name') }}"
        placeholder="{{ $attributes->get('placeholder') }}"
        value="{{ old($attributes->get('name'), $attributes->get('value')) }}" 
        @if($attributes->get('readonly')) readonly @endif
        @if($attributes->get('required')) required @endif
        @if($attributes->get('disabled')) disabled @endif
        {{ $attributes->except(['type', 'class', 'id', 'name', 'placeholder', 'value', 'readonly', 'required','disabled']) }}>
    <div class="invalid-feedback" id="{{ $attributes->get('id') ?? $attributes->get('name') }}Error">
        @error($attributes->get('name'))
            {{ $message }}
        @enderror
    </div>
</div>
