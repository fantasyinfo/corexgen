@extends('layout.app')

@section('content')
    @push('style')
        <style>
            .tox-promotion {
                height: none !important;
            }
        </style>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="justify-content-md-center col-lg-12">
                <div class="card stretch stretch-full">


                    <form id="templateForm" action="{{ route(getPanelRoutes($store)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{$template->id}}" />
                        <div class="card-body general-info">
                            <div class="mb-5 d-flex align-items-center justify-content-between">
                                <p class="fw-bold mb-0 me-4">
                                    <span class="d-block">{{ __('templates.Update Template') }}</span>
                                    <span
                                        class="fs-12 fw-normal text-muted text-truncate-1-line">{{ __('crud.Please add correct information') }}</span>
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span>{{ __('templates.Update Template') }}</span>
                                </button>
                            </div>

                            <div class="row mb-4 align-items-center">

                                <x-form-components.input-label for="templateTitle" class="custom-class" required>
                                    {{ __('templates.Title') }}
                                </x-form-components.input-label>

                                <x-form-components.input-group type="text" name="title" id="templateTitle"
                                    placeholder="{{ __('X Services / X Products Proposals') }}"
                                    value="{{ old('title', $template->title) }}" required class="custom-class" />

                            </div>

                            <div class="row mb-4 align-items-center">


                                <x-form-components.input-label for="template_details" class="custom-class">
                                    {{ __('templates.Description') }}
                                </x-form-components.input-label>

                                <x-form-components.textarea-group name="template_details" id="template_details"
                                    placeholder="Design template" value="{{ old('template_details') }}"
                                    class="custom-class template_details" />

                            </div>


                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>

    <script>
        let currentTheme = document.documentElement.getAttribute('data-bs-theme');

        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '.template_details',
                height: 600,
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
                menubar: true,
                setup: function(editor) {
                    editor.on('init', function() {
                        editor.setContent(`{!! $template->template_details !!}`);
                    });
                },
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
    </script>
@endpush
