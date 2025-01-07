@extends('layout.app')

@section('content')
    @php
        // prePrintR($estimate->toArray());
        $type = $estimate->typable_type === App\Models\CRM\CRMClients::class ? 'client' : 'lead';
    @endphp

    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="estimatesFieldsForm" action="{{ route(getPanelRoutes('estimates.update')) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{$estimate->id}}" />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('estimates.Update Estimate') }}</span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('estimates.Update Estimate') }}
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="type" required>
                                        {{ __('estimates.Select Type') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="type" id="type" class="form-select">
                                        <option value="client" {{ $type == 'client' ? 'selected' : '' }}>
                                            Client
                                        </option>
                                        <option value="lead" {{ $type == 'lead' ? 'selected' : '' }}>
                                            Lead
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div id="clientSection"
                                style="display:  {{ old('type', $estimate->typable_type) === App\Models\CRM\CRMClients::class ? 'flex' : 'none' }};">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="client_id" required>
                                        {{ __('estimates.Select Client') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="client_id" id="client_id" class="form-select searchSelectBox">
                                        @foreach ($clients as $item)
                                            @php
                                                $nameAndEmail = $item->first_name . ' ' . $item->last_name;
                                                if ($item->type == 'Company') {
                                                    $nameAndEmail = $item->company_name;
                                                }
                                                $nameAndEmail .= !$item->primary_email
                                                    ? ' [No Email Found...] '
                                                    : " [ $item->primary_email ]";
                                            @endphp
                                            <option value="{{ $item->id }}"
                                                {{ old('client_id', $estimate->typable_id) == $item->id ? 'selected' : '' }}>
                                                {{ $nameAndEmail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="leadSection"
                                style="display:  {{ old('type', $estimate->typable_type) === App\Models\CRM\CRMLeads::class ? 'flex' : 'none' }};">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="lead_id" required>
                                        {{ __('estimates.Select Lead') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="lead_id" id="lead_id" class="form-select searchSelectBox">
                                        @foreach ($leads as $item)
                                            @php
                                                $nameAndEmail = $item->first_name . ' ' . $item->last_name;
                                                if ($item->type == 'Company') {
                                                    $nameAndEmail = $item->company_name;
                                                }
                                                $nameAndEmail .= !$item->email
                                                    ? ' [No Email Found...] '
                                                    : " [ $item->email ]";
                                            @endphp
                                            <option value="{{ $item->id }}"
                                                {{ old('lead_id', $estimate->typable_id) == $item->id ? 'selected' : '' }}>
                                                {{ $nameAndEmail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="_prefix" class="custom-class" required>
                                        {{ __('estimates.Prefix') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="_prefix"
                                        name="_prefix" placeholder="{{ __('PRO-') }}"
                                        value="{{ old('_prefix', $estimate->_prefix) }}" disabled="true" required
                                        oninput="updateIdField()" />
                                </div>

                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="_id" class="custom-class" required>
                                        {{ __('estimates.ID') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group-prepend-append
                                    prepend="{{getSettingValue('Estimate Prefix')}}-"
                                    append="..." type="text" class="custom-class" id="_id"
                                        name="_id" placeholder="{{ __('0001') }}" disabled="true"
                                        value="{{ old('_id', $estimate->_id) }}" required />
                                </div>
                                <p class="offset-lg-4 font-12 my-2 text-secondary">
                                    <span class="text-success"> Auto-Increment (Last ID + 1)</span> by default. Can be
                                    modify also.
                                </p>
                            </div>

                            <hr>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="title" class="custom-class" required>
                                        {{ __('estimates.Title') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="title"
                                        name="title" placeholder="{{ __('Amazing Estimate title') }}"
                                        value="{{ old('title', $estimate->title) }}" required />

                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="value" class="custom-class">
                                        {{ __('estimates.Value') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group-prepend-append type="number" class="custom-class"
                                        id="value" 
                                        step="0.001"
                                        prepend="{{ getSettingValue('Currency Symbol') }}"
                                        append="{{ getSettingValue('Currency Code') }}"
                                         name="value"
                                        placeholder="{{ __('99999') }}" value="{{ old('value', $estimate->value) }}" />

                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="template_id">
                                        {{ __('estimates.Select Template') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="template_id" id="template_id" class="form-select">
                                        <option value="">Select Estimate Template (optional)</option>
                                        @foreach ($templates as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('template_id', $estimate->template_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="creating_date" class="custom-class" required>
                                        {{ __('estimates.Date') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="creating_date"
                                        name="creating_date" value="{{ old('creating_date', $estimate->creating_date) }}"
                                        required />

                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="valid_date" class="custom-class">
                                        {{ __('estimates.Valid Till') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" placeholder="Select Date" class="custom-class" id="valid_date"
                                        name="valid_date" value="{{ old('valid_date', $estimate->valid_date) }}" />
                                </div>
                            </div>
                            <hr>
                            <div class="row mb-4 align-items-center">


                                <x-form-components.input-label for="valid_date" class="custom-class">
                                    {{ __('estimates.Details') }}
                                </x-form-components.input-label>


                                <x-form-components.textarea-group name="details" id="details"
                                    placeholder="Extra details, conditions, rules, commitments, products, services, discouts, tax ... if any"
                                    value="{{ old('details', $estimate->details) }}" class="custom-class details" />

                            </div>

                            <hr>
                            @include('dashboard.crm.estimates.components._itemUpdate')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>


        <script>
            let currentTheme = document.documentElement.getAttribute('data-bs-theme');

            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '.details',
                    height: 300,
                    base_url: '/js/tinymce',
                    license_key: 'gpl',
                    valid_elements: '+*[*]',
                    width: '100%',
                    inline_styles: true,
                    keep_styles: true,
                    extended_valid_elements: '+*[*]',
                    custom_elements: '*',
                    invalid_elements: '',
                    verify_html: false,
                    valid_children: '+body[style]',

                    content_style: 'body { font-family: Arial, sans-serif; }', // Optional: add inline styling for the editor content

                    skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    content_css: currentTheme === 'dark' ? 'dark' : 'default',
                    setup: function(editor) {
                        editor.on('init', function() {
                            editor.setContent(`{!! $estimate->details !!}`);
                        });
                    },
                    menubar: true,
                    plugins: [
                        'accordion',
                        'advlist',
                        'anchor',
                        'autolink',
                        // 'autoresize',
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
                        'wordcount',
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | \alignleft aligncenter alignright alignjustify | \bullist numlist outdent indent | removeformat | help | \link image media preview codesample table code'
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const clientSection = document.getElementById('clientSection');
                const leadSection = document.getElementById('leadSection');
                const clientSelect = document.getElementById('client_id');
                const leadSelect = document.getElementById('lead_id');

                // Function to toggle sections and reset form fields
                function toggleSections(type, isInitialLoad = false) {
                    if (type === 'client') {
                        clientSection.style.display = 'flex';
                        leadSection.style.display = 'none';
                        // Only clear lead value if not initial load
                        if (!isInitialLoad) {
                            leadSelect.value = '';
                        }
                        clientSelect.required = true;
                        leadSelect.required = false;
                    } else if (type === 'lead') {
                        clientSection.style.display = 'none';
                        leadSection.style.display = 'flex';
                        // Only clear client value if not initial load
                        if (!isInitialLoad) {
                            clientSelect.value = '';
                        }
                        clientSelect.required = false;
                        leadSelect.required = true;
                    }
                }


              

                // Event listener for type change
                typeSelect.addEventListener('change', function() {
                    toggleSections(this.value, false);
                });

                // ID prefix handling
                function updateIdField() {
                    const prefix = document.getElementById('_prefix').value;
                    const idField = document.getElementById('_id');
                    if (!idField.dataset.modified) { // Only update if user hasn't modified `_id`
                        idField.value = `${prefix}0001`;
                    }
                }

                // Track manual changes to `_id`
                const idField = document.getElementById('_id');
                if (idField) {
                    idField.addEventListener('input', function() {
                        this.dataset.modified = true;
                    });
                }
            });
        </script>
    @endpush
@endsection
