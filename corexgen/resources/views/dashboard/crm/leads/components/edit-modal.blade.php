<!-- Modal -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-labelledby="editLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header card-bg border-b-1">
                <h5 class="modal-title" id="editLeadModalLabel">
                    <i class="fas fa-edit me-2"></i>Update Lead
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <form id="leadEditForm" method="POST">
                    <input type="hidden" name="id" />

                    <!-- Navigation Pills -->
                    <div class="row g-0">
                        <div class="col-md-3 border-end">
                            <div class="nav flex-column nav-pills p-3" id="v-pills-tab" role="tablist">
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

                            </div>
                        </div>


                        <!-- Tab Content -->
                        <div class="col-md-9">
                            <div class="tab-content p-4" id="v-pills-tabContent">
                                <!-- General Tab -->
                                <div class="tab-pane fade show active" id="v-general">
                                    <div class="mb-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" name="type" id="clientType">
                                            <option value="Individual">Individual</option>
                                            <option value="Company">Company</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="company_name_div">
                                        <label class="form-label">Company Name</label>
                                        <input type="text" class="form-control" name="company_name">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="first_name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="last_name" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leads Tab -->
                                <div class="tab-pane fade" id="v-leads">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="value">
                                            <span class="input-group-text">USD</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Groups</label>
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
                                                        <i class="fas fa-dot-circle"></i> {{ $ls->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                      
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" name="priority">
                                                <option value="Low">Low</option>
                                                <option value="Medium">Medium</option>
                                                <option value="High">High</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Stage</label>
                                            <select class="form-select" name="status_id" id="status_id" required>
                                                @foreach ($leadsStatus as $lst)
                                                    <option value="{{ $lst->id }}" <i class="fas fa-dot-circle">
                                                        </i> {{ $lst->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="mb-3">
                                            <label class="form-label">Assign To</label>
                                            <div class="col-lg-8">
                                                <x-form-components.dropdown-with-profile :title="'Select Team Members'" :options="$teamMates"
                                                    :name="'assign_to'" :multiple="true" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                    <!-- Contact Tab -->
                                    <div class="tab-pane fade" id="v-contact">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Phone</label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Preferred Contact Method</label>
                                            <select class="form-select" name="preferred_contact_method">
                                                <option value="Email">Email</option>
                                                <option value="Phone">Phone</option>
                                                <option value="In-Person">In-Person</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Address Tab -->
                                    <div class="tab-pane fade" id="v-address">
                                        <div class="mb-3">
                                            <label class="form-label">Street Address</label>
                                            <textarea class="form-control" name="address[street_address]" rows="3"></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Country</label>
                                                <select class="form-control searchSelectBox "
                                                    name="address.country_id" id="country_id">
                                                    <option value="0" selected> ----- Select Country ----------
                                                    </option>
                                                    @if ($countries)
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}">
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option disabled>No country available</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" name="address[city_name]">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" name="address[pincode]">
                                        </div>
                                    </div>

                                    <!-- Additional Tab -->
                                    <div class="tab-pane fade" id="v-additional">
                                        <div class="mb-3">
                                            <label class="form-label">Additional Details</label>
                                            <textarea class="form-control wysiwyg-editor" name="details" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer card-bg">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>




@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navButtons = document.querySelectorAll('#v-pills-tab button');
            navButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                });
            });
        });
    </script>
@endpush
