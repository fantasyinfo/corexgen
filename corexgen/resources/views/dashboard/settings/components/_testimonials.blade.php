<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="TestimonialsHeading" class="custom-class" required>
        {{ __('frontend.Testimonials Heading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsHeading" name="testimonials[Heading]"
        value="{{ old('testimonials[Heading]', $testimonialsSection['Heading']) }}" required />
</div>
<div class="row mb-4 align-items-center">
    <x-form-components.input-label for="TestimonialsSubHeading" class="custom-class" required>
        {{ __('frontend.Testimonials SubHeading') }} </x-form-components.input-label>

    <x-form-components.input-group type="text" class="custom-class" id="testimonialsSubHeading"
        name="testimonials[SubHeading]" value="{{ old('testimonials[SubHeading]', $featuresSection['SubHeading']) }}"
        required />
</div>
@if (isset($testimonialsSection['Options']))
    @foreach ($testimonialsSection['Options'] as $key => $op)
        <div class="card my-1">
            <div class="card-header">
                <h6>Testimonials</h6>
            </div>
            <div class="card-body">
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="TestimonialsMessage" class="custom-class" required>
                        {{ __('frontend.Testimonials Message') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsMessage"
                        name="testimonials[Options][Message][]"
                        value="{{ old('testimonials[Options][Message][]', $op['Message']) }}" required />
                </div>
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="TestimonialsCustomer" class="custom-class" required>
                        {{ __('frontend.Testimonials Customer') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsCustomer"
                        name="testimonials[Options][Customer Name][]"
                        value="{{ old('testimonials[Options][Customer Name][]', $op['Customer Name']) }}" required />
                </div>
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="TestimonialsPosition" class="custom-class" required>
                        {{ __('frontend.Testimonials Position') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsPosition"
                        name="testimonials[Options][Position][]"
                        value="{{ old('testimonials[Options][Position][]', $op['Position']) }}" required />
                </div>
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="TestimonialsCompany" class="custom-class" required>
                        {{ __('frontend.Testimonials Company') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsCompany"
                        name="testimonials[Options][Company][]"
                        value="{{ old('testimonials[Options][Company][]', $op['Company']) }}" required />
                </div>
                <div class="row mb-4 align-items-center">
                    <x-form-components.input-label for="TestimonialsLOGO" class="custom-class" required>
                        {{ __('frontend.Testimonials LOGO') }}
                    </x-form-components.input-label>

                    <x-form-components.input-group type="text" class="custom-class" id="TestimonialsLOGO"
                        name="testimonials[Options][LOGO][]"
                        value="{{ old('testimonials[Options][LOGO][]', $op['LOGO']) }}" required />
                </div>
            </div>
        </div>
    @endforeach
@endif
