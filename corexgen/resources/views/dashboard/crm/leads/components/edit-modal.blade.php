<!-- Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header card-bg border-b-1">
                <h5 class="modal-title" id="editLeadModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="leadEditForm" method="POST" action="{{ route(getPanelRoutes('leads.update')) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" />
                    <input type="hidden" name="from_kanban" value="true" />
                    <!-- Navigation Pills -->
                    <div class="row g-0">
                        <div class="col-md-3 kanban-border">
                            <div class="nav flex-column nav-pills p-3 text-left" id="v-pills-tab" role="tablist">
                                <button type="button" class="nav-link active mb-2" data-bs-toggle="pill"
                                    data-bs-target="#v-general">
                                    <i class="fas fa-info-circle me-2"></i>General
                                </button>
                                <button type="button" class="nav-link mb-2" data-bs-toggle="pill"
                                    data-bs-target="#v-leads">
                                    <i class="fas fa-chart-line me-2"></i>Lead Details
                                </button>
                                <button type="button" class="nav-link mb-2" data-bs-toggle="pill"
                                    data-bs-target="#v-contact">
                                    <i class="fas fa-address-book me-2"></i>Contact
                                </button>
                                <button type="button" class="nav-link mb-2" data-bs-toggle="pill"
                                    data-bs-target="#v-address">
                                    <i class="fas fa-map-marker-alt me-2"></i>Address
                                </button>
                                <button type="button" class="nav-link mb-2" data-bs-toggle="pill"
                                    data-bs-target="#v-additional">
                                    <i class="fas fa-plus-circle me-2"></i>Additional
                                </button>

                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <button class="nav-link mb-2" data-bs-toggle="pill" data-bs-target="#custom-fields"
                                        type="button">
                                        <i class="fas fa-plus me-2"></i>Custom Fields
                                    </button>
                                @endif

                            </div>
                        </div>


                        <!-- Tab Content -->
                        <div class="col-md-9 kanban-border">
                            <div class="tab-content p-4" id="v-pills-tabContent">
                                <!-- General Tab -->
                                <div class="tab-pane fade show active" id="v-general">

                                    <x-form-components.modal-tabs-heading :type="'secondary'" :icon="'fa-info-circle'"
                                        :title="'General'" />


                                    <div class="mb-3">
                                        <x-form-components.input-label for="clientType" required>
                                            {{ __('leads.Type') }}
                                        </x-form-components.input-label>
                                        <select class="form-select" name="type" id="clientType" required>
                                            <option value="Individual">Individual</option>
                                            <option value="Company">Company</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="company_name_div">
                                        <x-form-components.input-label for="companyName" required>
                                            {{ __('leads.Company Name') }}
                                        </x-form-components.input-label>
                                        <x-form-components.input-group type="text" name="company_name"
                                            id="companyName" placeholder="{{ __('Abc Pvt Ltd') }}"
                                            value="{{ old('company_name') }}" />
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="firstName" required>
                                                {{ __('leads.First Name') }}
                                            </x-form-components.input-label>
                                            <x-form-components.input-group type="text" name="first_name"
                                                id="firstName" placeholder="{{ __('First Name') }}"
                                                value="{{ old('first_name') }}" required />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="lastName" required>
                                                {{ __('leads.Last Name') }}
                                            </x-form-components.input-label>
                                            <x-form-components.input-group type="text" name="last_name"
                                                id="lastName" placeholder="{{ __('Last Name') }}"
                                                value="{{ old('last_name') }}" required />
                                        </div>
                                    </div>
                                </div>

                                <!-- Leads Tab -->
                                <div class="tab-pane fade" id="v-leads">
                                    <x-form-components.modal-tabs-heading :type="'secondary'" :icon="'fa-chart-line'"
                                        :title="'Lead Details'" />

                                    <div class="mb-3">
                                        <x-form-components.input-label for="title" required>
                                            {{ __('leads.Title') }}
                                        </x-form-components.input-label>
                                        <x-form-components.input-group type="text" name="title" id="title"
                                            placeholder="{{ __('New Development Project Lead') }}"
                                            value="{{ old('title') }}" required />
                                    </div>

                                    <div class="mb-3">
                                        <x-form-components.input-label for="value">
                                            {{ __('leads.Value') }}
                                        </x-form-components.input-label>
                                        <div class="input-group">
                                            <x-form-components.input-group-prepend-append prepend="$"
                                                append="USD" type="number" name="value" id="value"
                                                placeholder="{{ __('New Development Project Lead') }}"
                                                value="{{ old('value') }}" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="group_id">
                                                {{ __('leads.Groups') }}
                                            </x-form-components.input-label>
                                            <select class="form-select" name="group_id" id="group_id">
                                                @foreach ($leadsGroups as $lg)
                                                    <option value="{{ $lg->id }}">
                                                        {{ $lg->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="source_id">
                                                {{ __('leads.Sources') }}
                                            </x-form-components.input-label>
                                            <select class="form-select" name="source_id" id="source_id">
                                                @foreach ($leadsSources as $ls)
                                                    <option value="{{ $ls->id }}">
                                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="priority" required>
                                                {{ __('leads.Priority') }}
                                            </x-form-components.input-label>
                                            <select class="form-select" name="priority">
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="status_id" required>
                                                {{ __('leads.Stage') }}
                                            </x-form-components.input-label>
                                            <select class="form-select" name="status_id" id="status_id" required>
                                                @foreach ($leadsStatus as $lst)
                                                    <option value="{{ $lst->id }}"
                                                        {{ old('status_id') == $lst->id ? 'selected' : '' }}> <i
                                                            class="fas fa-dot-circle"></i> {{ $lst->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    <div class="row">


                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <x-form-components.input-label for="last_contacted_date">
                                                    {{ __('leads.Last Contacted') }}
                                                </x-form-components.input-label>
                                                <x-form-components.input-group type="date"
                                                    name="last_contacted_date" id="last_contacted_date"
                                                    value="{{ old('last_contacted_date') }}" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <x-form-components.input-label for="last_activity_date">
                                                    {{ __('leads.Last Activity') }}
                                                </x-form-components.input-label>
                                                <x-form-components.input-group type="date"
                                                    name="last_activity_date" id="last_activity_date"
                                                    value="{{ old('last_activity_date') }}" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <x-form-components.input-label for="follow_up_date">
                                                    {{ __('leads.Follow Up') }}
                                                </x-form-components.input-label>
                                                <x-form-components.input-group type="date" name="follow_up_date"
                                                    id="follow_up_date" value="{{ old('follow_up_date') }}" />
                                            </div>
                                        </div>


                                    </div>

                                    

                                    <div class="row">

                                        <div class="mb-3">
                                            <x-form-components.input-label for="assign_to[]">
                                                {{ __('leads.Assign To') }}
                                            </x-form-components.input-label>


                                            <select class="form-control  searchSelectBoxKanban" multiple
                                                name="assign_to[]" id="assign_to">
                                                @if ($teamMates)
                                                    @foreach ($teamMates as $tm)
                                                        <option value="{{ $tm->id }}">
                                                            {{ $tm->name }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled>No team matest available</option>
                                                @endif
                                            </select>


                                        </div>
                                    </div>

                                    {{-- 
                                    Last Contacted	Sep 5, 2016, 12:00 AM
                                    Last Activity	Jul 29, 2010, 12:00 AM
                                    Follow Up --}}















                                </div>
                                <!-- Contact Tab -->
                                <div class="tab-pane fade" id="v-contact">
                                    <x-form-components.modal-tabs-heading :type="'secondary'" :icon="'fa-address-book'"
                                        :title="'Contact'" />
                                    <div class="mb-3">
                                        <x-form-components.input-label for="emails">
                                            {{ __('leads.Email') }}
                                        </x-form-components.input-label>
                                        <x-form-components.input-group type="email" name="email" id="email"
                                            placeholder="{{ __('Email Address') }}" value="{{ old('email') }}" />
                                    </div>

                                    <div class="mb-3">
                                        <x-form-components.input-label for="phones">
                                            {{ __('leads.Phone') }}
                                        </x-form-components.input-label>
                                        <x-form-components.input-group type="tel" name="phone" id="phone"
                                            placeholder="{{ __('Phone Number') }}" value="{{ old('phone') }}" />
                                    </div>

                                    <div class="mb-3">
                                        <x-form-components.input-label for="preferred_contact_method" required>
                                            {{ __('leads.Prefferd Contact') }}
                                        </x-form-components.input-label>
                                        <select class="form-select" name="preferred_contact_method">
                                            <option value="Email">Email</option>
                                            <option value="Phone">Phone</option>
                                            <option value="In-Person">In-Person</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Address Tab -->
                                <div class="tab-pane fade" id="v-address">
                                    <x-form-components.modal-tabs-heading :type="'secondary'" :icon="'fa-map-marker-alt'"
                                        :title="'Address'" />
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form-components.input-label for="compnayAddressStreet"
                                                class="custom-class">
                                                {{ __('address.Address') }}
                                            </x-form-components.input-label>
                                            <x-form-components.textarea-group name="address.street_address"
                                                id="compnayAddressStreet"
                                                placeholder="Enter Registered Street Address" class="custom-class"
                                                value="{{ old('address.street_address') }}" />
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="compnayAddressCountry"
                                                class="custom-class">
                                                {{ __('address.Country') }}
                                            </x-form-components.input-label>

                                            <select class="form-control" name="address.country_id" id="country_id">

                                                @if ($countries)
                                                    @foreach ($countries as $country)
                                                        <option value="{{ $country->id }}"
                                                            {{ old('address.country_id') == $country->id ? 'selected' : '' }}>
                                                            {{ $country->name }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option disabled>No country available</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <x-form-components.input-label for="compnayAddressCity"
                                                class="custom-class">
                                                {{ __('address.City') }}
                                            </x-form-components.input-label>
                                            <x-form-components.input-group type="text" name="address.city_name"
                                                id="compnayAddressCity" placeholder="{{ __('Enter City') }}"
                                                value="{{ old('address.city_name') }}" class="custom-class" />
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <x-form-components.input-label for="compnayAddressPincode"
                                            class="custom-class">
                                            {{ __('address.Pincode') }}
                                        </x-form-components.input-label>
                                        <x-form-components.input-group type="text" name="address.pincode"
                                            id="compnayAddressPincode" placeholder="{{ __('Enter Pincode') }}"
                                            value="{{ old('address.pincode') }}" class="custom-class" />
                                    </div>
                                </div>

                                <!-- Additional Tab -->
                                <div class="tab-pane fade" id="v-additional">
                                    <x-form-components.modal-tabs-heading :type="'secondary'" :icon="'fa-plus-circle'"
                                        :title="'Additional'" />
                                    <div class="mb-3">
                                        <x-form-components.input-label for="details">
                                            {{ __('leads.Additional Details') }}
                                        </x-form-components.input-label>
                                        <textarea name="details" id="details" class="form-control wysiwyg-editor" rows="5">{{ old('details') }}</textarea>
                                    </div>
                                </div>

                                <!-- Custom Fields Tab -->
                                @if (isset($customFields) && $customFields->isNotEmpty())
                                    <x-form-components.custom-fields-create :customFields="$customFields" />
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer card-bg">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navButtons = document.querySelectorAll('#v-pills-tab button');
            navButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                });
            });


            if ($(".searchSelectBoxKanban").length > 0) {
                $(".searchSelectBoxKanban").select2({
                    'placeholder': 'Please select an option',
                    'dropdownParent': $('#editLeadModal')
                });
            }


            const currentTheme = document.documentElement.getAttribute('data-bs-theme');

            tinymce.init({
                selector: '.wysiwyg-editor',
                height: 300,
                base_url: '/js/tinymce',
                license_key: 'gpl',
                skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                content_css: currentTheme === 'dark' ? 'dark' : 'default',
                menubar: false,
                plugins: [
                    'accordion',
                    'advlist',
                    'anchor',
                    'autolink',
                    'autoresize',
                    'autosave',
                    'charmap',
                    'code',
                    'codesample',
                    'directionality',
                    'emoticons',
                    'fullscreen',
                    'help',
                    'lists',
                    'link',
                    'image',
                    'preview',
                    'anchor',
                    'searchreplace',
                    'visualblocks',
                    'insertdatetime',
                    'media',
                    'table',
                    'wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | \
                                          alignleft aligncenter alignright alignjustify | \
                                          bullist numlist outdent indent | removeformat | help | \
                                          link image media preview codesample table'
            });








        });
    </script>
@endpush
