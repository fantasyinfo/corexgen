@php
    // prePrintR($client->attachments->toArray());
@endphp

@push('style')
    <style>
        /* File Upload Styling */
        .file-upload-label {
            border: 2px dashed var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .file-upload-label:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .file-upload-label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background-color: var(--body-bg);
            transition: all 0.3s ease;
        }

        .file-item:hover {
            background-color: var(--body-bg);
            border-color: var(--primary-color);
        }

        .file-name {
            font-weight: 600;
            color: var(--body-color);
        }

        .file-size {
            font-size: 0.875rem;
            color: #666;
        }


        .remove-file-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.25rem;
            cursor: pointer;
            margin-left: 1rem;
            transition: color 0.3s ease;
        }

        .remove-file-btn:hover {
            color: red;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            background-color: var(--body-bg);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .attachment-item:hover {
            background-color: var(--body-bg);
            border-color: var(--primary-color);
        }

        .attachment-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: #fff;
            margin-right: 1rem;
            overflow: hidden;
        }

        .attachment-icon img.attachment-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .attachment-header {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .attachment-actions {
            display: flex;
            gap: 0.5rem;
        }

        .attachment-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Responsive Styling */
        .attachment-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            background-color: var(--body-bg);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .attachment-icon {
            flex: 0 0 50px;
            margin-right: 1rem;
        }

        .attachment-content {
            flex: 1 1 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .attachment-header {
            flex: 1 1 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .attachment-name {
            max-width: calc(100% - 100px);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attachment-actions {
            flex: 0 0 auto;
            display: flex;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .attachment-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .attachment-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .attachment-header {
                flex-wrap: wrap;
                margin-bottom: 0.5rem;
            }

            .attachment-actions {
                margin-top: 0.5rem;
            }

            .attachment-name {
                max-width: 100%;
            }
        }
    </style>
@endpush
<div class="files-section">
    <h6><i class="fas fa-paperclip me-2"></i> Attachments</h6>
    <div class="row">
        <div class="col-lg-12">
            <div class="file-upload-area mb-3 kanban-border">
                <form id="mediaForm" method="POST" action="{{ route(getPanelRoutes('clients.attachment.create')) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $client->id }}" />
                    <input type="file" multiple class="d-none" name="files[]" id="fileUpload">
                    <label for="fileUpload" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Drop files here or click to upload</span>
                    </label>
                    <div class="file-list my-2">
                        <ul id="fileList"></ul>
                    </div>
                    <div class="d-flex justify-content-end my-2 mw-2">
                        <button class="btn btn-primary mt-2 mx-2" type="submit">Upload</button>
                    </div>
                </form>
            </div>

            <div class="attachments-list">
                @php
                    $deletePermission =
                        isset($permissions['DELETE']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']);
                @endphp
                @if ($client->attachments->count() > 0)
                    @foreach ($client->attachments as $attachment)
                        <div class="attachment-item mb-4" data-id="{{ $attachment->id }}">
                            <div class="d-flex">
                                <div class="attachment-icon">
                                    @if (in_array($attachment->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ $attachment->file_path }}" alt="{{ $attachment->file_name }}"
                                            class="attachment-preview" />
                                    @else
                                        <i class="fas fa-file"></i>
                                    @endif
                                </div>
                                <div class="attachment-content ms-3">
                                    <div
                                        class="attachment-header text-muted d-flex justify-content-between align-items-center">
                                        <h6 class="attachment-name mb-0">
                                            {{ $attachment->file_name }}
                                            <small class="text-muted ms-2">
                                                ({{ number_format($attachment->size / 1024, 2) }} KB)
                                            </small>
                                        </h6>
                                        <div class="attachment-actions">
                                            <a href="{{ $attachment->file_path }}"
                                                class="btn btn-outline-secondary btn-sm" target="_blank"
                                                title="View Attachment">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ $attachment->file_path }}"
                                                class="btn btn-outline-secondary btn-sm" download
                                                title="Download Attachment">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if ($deletePermission)
                                                <button class="btn btn-danger btn-sm delete-attachment"
                                                    data-url="{{ route(getPanelRoutes('clients.attachment.destroy'), ['id' => $attachment->id]) }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-attachments text-center py-4">
                        <i class="far fa-file-alt fa-2x text-muted"></i>
                        <p class="mt-2 text-muted">No attachments yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>



@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize variables
            let selectedFiles = [];
            const fileUploadInput = $('#fileUpload');
            const fileListContainer = $('#fileList');
            const fileUploadArea = $('.file-upload-area');
            const maxFileSize = 10 * 1024 * 1024; // 10MB limit
            const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token properly

            // Handle file selection
            fileUploadInput.on('change', function() {
                addFiles(this.files);
            });

            // Drag-and-drop support
            fileUploadArea
                .on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('drag-over');
                })
                .on('dragleave drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');
                    if (e.type === 'drop') {
                        addFiles(e.originalEvent.dataTransfer.files);
                    }
                });

            // Add files to the list with validation
            function addFiles(files) {
                Array.from(files).forEach((file) => {
                    // Check file size
                    if (file.size > maxFileSize) {
                        alert(`File ${file.name} is too large. Maximum size is ${maxFileSize/1024/1024}MB`);
                        return;
                    }

                    // Check for duplicates
                    if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        selectedFiles.push(file);
                    }
                });
                renderFileList();
            }

            // Render file list
            function renderFileList() {
                fileListContainer.empty();
                selectedFiles.forEach((file, index) => {
                    fileListContainer.append(`
                        <li class="file-item">
                            <div>
                                <span class="file-name">${truncateFileName(file.name, 30)}</span>
                                <span class="file-size">(${(file.size / 1024).toFixed(2)} KB)</span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-file" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </li>
                    `);
                });
            }

            // Remove file from the list
            fileListContainer.on('click', '.remove-file', function() {
                const index = $(this).data('index');
                selectedFiles.splice(index, 1);
                renderFileList();
            });

            // Truncate long file names
            function truncateFileName(name, maxLength) {
                return name.length > maxLength ? `${name.substring(0, maxLength - 3)}...` : name;
            }

            // Handle file upload via AJAX
            $('#mediaForm').submit(function(e) {
                e.preventDefault();

                if (selectedFiles.length === 0) {
                    alert('Please select files to upload');
                    return;
                }

                const formData = new FormData();
                formData.append('_token', csrfToken); // Add CSRF token to FormData
                formData.append('id', $('input[name="id"]').val());
                selectedFiles.forEach(file => formData.append('files[]', file));

                // Disable submit button during upload
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken // Add CSRF token to headers
                    },
                    success: function(response) {




                        let isDeletePermission =
                            "{{ hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']) }}";

                        response.attachments.forEach((attachment) => {
                            let deleteURL =
                                "{{ route(getPanelRoutes('clients.attachment.destroy'), ['id' => ':id']) }}";
                            deleteURL = deleteURL.replace(':id', attachment.id);

                            const deleteButton = isDeletePermission ?
                                `<button class="btn btn-danger btn-sm delete-attachment" data-url="${deleteURL}">
                                      <i class="fas fa-trash"></i>
                                   </button>` :
                                '';

                            $('.attachments-list').prepend(`
                                <div class="attachment-item mb-4" data-id="${attachment.id}">
                                    <div class="d-flex">
                                        <div class="attachment-icon">
                                            ${attachment.file_extension.match(/(jpg|jpeg|png|gif|webp)/i)
                                                ? `<img src="${attachment.file_path}" alt="${attachment.file_name}" class="attachment-preview" />`
                                                : `<i class="fas fa-file"></i>`}
                                        </div>
                                        <div class="attachment-content ms-3">
                                            <div class="attachment-header text-muted d-flex justify-content-between align-items-center">
                                                <h6 class="attachment-name mb-0">
                                                    ${attachment.file_name}
                                                    <small class="text-muted ms-2">(${(attachment.size / 1024).toFixed(2)} KB)</small>
                                                </h6>
                                                <div class="attachment-actions">
                                                    <a href="${attachment.file_path}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="${attachment.file_path}" download class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    ${deleteButton}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });

                        selectedFiles = [];
                        renderFileList();
                        alert('Files uploaded successfully.');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'An error occurred during upload.');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                    }
                });
            });

            // Delete attachment via AJAX
            $(document).on('click', '.delete-attachment', function() {
                const button = $(this);
                const url = button.data('url');

                if (confirm('Are you sure you want to delete this attachment?')) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: csrfToken
                        },
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function() {
                            button.closest('.attachment-item').remove();
                            alert('Attachment deleted successfully.');
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message ||
                                'An error occurred while deleting the attachment.');
                        }
                    });
                }
            });
        });
    </script>
@endpush
