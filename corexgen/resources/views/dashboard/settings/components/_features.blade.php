<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="FeaturesHeading" class="custom-class" required>
        {{ __('frontend.Features Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="FeaturesHeading"
        name="features[Heading]"
        value="{{ old('features[Heading]', $featuresSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="FeaturesSubHeading" class="custom-class" required>
        {{ __('frontend.Features SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class"
        id="FeaturesSubHeading" name="features[SubHeading]"
        value="{{ old('features[SubHeading]', $featuresSection['SubHeading']) }}"
        required />
</div>
@if (isset($featuresSection['Options']))
    @foreach ($featuresSection['Options'] as $key => $op)
        <div class="card my-1">
            <div class="card-header">
                <h6>Features</h6>
            </div>
            <div class="card-body">
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="FeaturesHeading"
                        class="custom-class" required>
                        {{ __('frontend.Features Heading') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class"
                        id="FeaturesHeading" name="features[Options][Heading][]"
                        value="{{ old('features[Options][Heading][]', $op['Heading']) }}"
                        required />
                </div>
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="FeaturesSubHeading"
                        class="custom-class" required>
                        {{ __('frontend.Features SubHeading') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class"
                        id="FeaturesSubHeading" name="features[Options][SubHeading][]"
                        value="{{ old('features[Options][SubHeading][]', $op['SubHeading']) }}"
                        required />
                </div>
            </div>
        </div>
    @endforeach
@endif