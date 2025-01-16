<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="FooterHeading" class="custom-class" required>
        {{ __('frontend.Footer Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="FooterHeading" name="footer[Heading]"
        value="{{ old('footer[Heading]', $footerSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="FooterSubHeading" class="custom-class" required>
        {{ __('frontend.Footer SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="FooterSubHeading" name="footer[SubHeading]"
        value="{{ old('footer[SubHeading]', $footerSection['SubHeading']) }}" required />
</div>
