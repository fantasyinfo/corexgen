<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="SolutionsHeading" class="custom-class" required>
        {{ __('frontend.Solutions Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="SolutionsHeading" name="solutions[Heading]"
        value="{{ old('solutions[Heading]', $solutionsSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="SolutionsSubHeading" class="custom-class" required>
        {{ __('frontend.Solutions SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="SolutionsSubHeading"
        name="solutions[SubHeading]" value="{{ old('solutions[SubHeading]', $solutionsSection['SubHeading']) }}"
        required />
</div>
@if (isset($solutionsSection['Options']))
    @foreach ($solutionsSection['Options'] as $key => $op)
        <div class="card my-1">
            <div class="card-header">
                <h6>Solutions </h6>
            </div>
            <div class="card-body">
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-group type="text" class="custom-class" id="FeaturesHeading"
                        name="solutions[Options][]" value="{{ old('solutions[Options][]', $op) }}" required />
                </div>

            </div>
        </div>
    @endforeach
@endif
