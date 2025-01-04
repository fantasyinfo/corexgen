@extends('layout.app')

@section('content')
    @php

        $type = null;
        $id = null;
        $refrer = null;
        if (isset($_GET['type']) && isset($_GET['id']) && isset($_GET['refrer'])) {
            $type = trim($_GET['type']);
            $id = trim($_GET['id']);
            $refrer = trim($_GET['refrer']);
        }

        // prePrintR($tax->toArray());

    @endphp
    
    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-12">
                <div class="card stretch stretch-full">
                    <form id="proposalsFieldsForm" action="{{ route(getPanelRoutes('proposals.store')) }}" method="POST">
                        @csrf

                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('proposals.Create New Proposal') }}</span>
                                    <span class="fs-12 fw-normal text-muted text-truncate-1-line">
                                        {{ __('crud.Please add correct information') }}
                                    </span>
                                </p>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('proposals.Create Proposal') }}
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="type" required>
                                        {{ __('proposals.Select Type') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="type" id="type" class="form-select">
                                        <option value="client" {{ old('type') == 'client' ? 'selected' : '' }}
                                            {{ $type == 'client' ? 'selected' : '' }}>Client
                                        </option>
                                        <option value="lead" {{ old('type') == 'lead' ? 'selected' : '' }}
                                            {{ $type == 'lead' ? 'selected' : '' }}>Lead</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center" id="clientSection" style="display: none;">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="client_id" required>
                                        {{ __('proposals.Select Client') }}
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
                                                {{ old('client_id') == $item->id ? 'selected' : '' }}
                                                {{ $id == $item->id ? 'selected' : '' }}>{{ $nameAndEmail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3 align-items-center" id="leadSection" style="display: none;">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="lead_id" required>
                                        {{ __('proposals.Select Lead') }}
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
                                                {{ old('lead_id') == $item->id ? 'selected' : '' }}
                                                {{ $id == $item->id ? 'selected' : '' }}>{{ $nameAndEmail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="_prefix" class="custom-class" required>
                                        {{ __('proposals.Prefix') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="_prefix"
                                        name="_prefix" placeholder="{{ getSettingValue('Proposal Prefix') }}"
                                        value="{{ old('_prefix', getSettingValue('Proposal Prefix')) }}" disabled />
                                </div>
                                <p class="offset-lg-4 font-12 my-2 text-secondary"> Can be modify under one word settings.
                                </p>

                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="_id" class="custom-class" required>
                                        {{ __('proposals.ID') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group-prepend-append
                                        prepend="{{ getSettingValue('Proposal Prefix') }}-" append="..." type="text"
                                        class="custom-class" id="_id" name="_id"
                                        placeholder="{{ __('0001') }}" value="{{ old('_id', $lastId) }}" required />
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
                                        {{ __('proposals.Title') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="text" class="custom-class" id="title"
                                        name="title" placeholder="{{ __('Amazing title') }}" value="{{ old('title') }}"
                                        required />

                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="value" class="custom-class">
                                        {{ __('proposals.Value') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group-prepend-append type="number" class="custom-class"
                                        id="value" step="0.001" prepend="{{ getSettingValue('Currency Symbol') }}"
                                        append="{{ getSettingValue('Currency Code') }}" name="value"
                                        placeholder="{{ __('99999') }}" value="{{ old('value') }}" />

                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3 align-items-center">
                                <div class="col-lg-4">
                                    <x-form-components.input-label for="template_id">
                                        {{ __('proposals.Select Template') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="template_id" id="template_id" class="form-select">
                                        <option value="">Select Template (optional)</option>
                                        @foreach ($templates as $item)
                                            <option value="{{ $item->id }}"
                                                {{ old('template_id') == $item->id ? 'selected' : '' }}>
                                                {{ $item->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="creating_date" class="custom-class" required>
                                        {{ __('proposals.Date') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" class="custom-class" id="creating_date"
                                        name="creating_date" value="{{ old('creating_date') }}" required />

                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">

                                    <x-form-components.input-label for="valid_date" class="custom-class">
                                        {{ __('proposals.Valid Till') }}
                                    </x-form-components.input-label>
                                </div>
                                <div class="col-lg-8">
                                    <x-form-components.input-group type="date" class="custom-class" id="valid_date"
                                        name="valid_date" value="{{ old('valid_date') }}" />
                                </div>
                            </div>


                            <hr>

                        
                           

                            <div class="row mb-4 align-items-center">


                                <x-form-components.input-label for="valid_date" class="custom-class">
                                    {{ __('proposals.Details') }}
                                </x-form-components.input-label>


                                <x-form-components.textarea-group name="details" id="details"
                                    placeholder="Extra details, conditions, rules, commitments, products, services, discouts, tax ... if any"
                                    value="{{ old('details') }}" class="custom-class details" />

                            </div>
                            <hr>
                            @include('dashboard.crm.proposals.components._itemCreate')
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

                    // skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                    // content_css: currentTheme === 'dark' ? 'dark' : 'default',
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
                function toggleSections(type) {
                    if (type === 'client') {
                        clientSection.style.display = 'flex';
                        leadSection.style.display = 'none';
                        leadSelect.value = '';
                        clientSelect.required = true;
                        leadSelect.required = false;
                    } else {
                        clientSection.style.display = 'none';
                        leadSection.style.display = 'flex';
                        clientSelect.value = '';
                        clientSelect.required = false;
                        leadSelect.required = true;
                    }
                }

                // Initial state
                toggleSections(typeSelect.value);

                // Event listener for type change
                typeSelect.addEventListener('change', function() {
                    toggleSections(this.value);
                });


                function updateIdField() {
                    const prefix = document.getElementById('_prefix').value;
                    const idField = document.getElementById('_id');
                    if (!idField.dataset.modified) { // Only update if user hasn't modified `_id`
                        idField.value = `${prefix}0001`;
                    }
                }

                // Track manual changes to `_id`
                document.getElementById('_id').addEventListener('input', function() {
                    this.dataset.modified = true;
                });
            });


         
        </script>
    @endpush
@endsection
