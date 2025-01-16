<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="PricingHeading" class="custom-class" required>
        {{ __('frontend.Pricing Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="PricingHeading" name="pricing[Heading]"
        value="{{ old('pricing[Heading]', $pricingSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="PricingSubHeading" class="custom-class" required>
        {{ __('frontend.Pricing SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="PricingSubHeading" name="pricing[SubHeading]"
        value="{{ old('pricing[SubHeading]', $pricingSection['SubHeading']) }}" required />
</div>
