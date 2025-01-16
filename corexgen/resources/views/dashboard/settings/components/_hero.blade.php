<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="HeroHeading" class="custom-class" required>
        {{ __('frontend.Hero Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="HeroHeading" name="hero[Heading]"
        value="{{ old('hero[Heading]', $heroSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="HeroSubHeading" class="custom-class" required>
        {{ __('frontend.Hero SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="HeroSubHeading" name="hero[SubHeading]"
        value="{{ old('hero[SubHeading]', $heroSection['SubHeading']) }}" required />
</div>
