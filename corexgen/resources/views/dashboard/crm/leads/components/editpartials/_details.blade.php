<div class="mt-4">
    <x-form-components.input-label for="detailsTextArea">
        {{ __('leads.Additional Details') }}
    </x-form-components.input-label>
    <textarea name="details" id="detailsTextArea" class="form-control wysiwyg-editor" rows="5">{{ old('details') }}</textarea>
</div>

@push('scripts')

    <script>
        // 
        // Initialize WYSIWYG editor
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '.wysiwyg-editor',
                height: 300,
                base_url: '/js/tinymce',
                license_key: 'gpl',
                skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                content_css: currentTheme === 'dark' ? 'dark' : 'default',
                setup: function(editor) {
                    editor.on('init', function() {
                        editor.setContent(`{!! $lead->details !!}`);
                    });
                },
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
        }
    </script>
@endpush
