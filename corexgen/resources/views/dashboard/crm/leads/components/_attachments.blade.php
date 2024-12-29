@php
    // prePrintR($lead->attachments->toArray());
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
    <div class="row">
        <div class="col-lg-12">
            <div class="file-upload-area mb-3">
                <form id="mediaForm" method="POST" action="{{ route(getPanelRoutes('leads.attachment.create')) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $lead->id }}" />
                    <input type="file" multiple class="d-none" name="files[]" id="fileUpload">
                    <label for="fileUpload" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Drop files here or click to upload</span>
                    </label>

                    <div class="file-list my-2">
                        <ul id="fileList"></ul>
                    </div>

                    <div class="d-flex justify-content-center my-2">
                        <button form="mediaForm" class="btn btn-primary mt-2 " type="submit">Upload</button>
                    </div>
                </form>
            </div>

            <div class="note-list">
                @php
                    $deletePermission = false;
                    if (
                        isset($permissions['DELETE']) &&
                        hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY'])
                    ) {
                        $deletePermission = true;
                    }
                @endphp
                @if ($lead->attachments->count() > 0)
                    @foreach ($lead->attachments as $attachment)
                        <div class="attachment-item mb-4">
                            <div class="d-flex">
                                <!-- Attachment Icon -->
                                <div class="attachment-icon">
                                    @if (in_array($attachment->file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                        <img src="{{ $attachment->file_path }}" alt="{{ $attachment->file_name }}"
                                            class="attachment-preview" />
                                    @else
                                        <i class="fas fa-file"></i>
                                    @endif
                                </div>

                                <!-- Attachment Content -->
                                <div class="attachment-content  ms-3">
                                    <div
                                        class="attachment-header text-muted d-flex gap-3 justify-content-between align-items-center">
                                        <h6 class="attachment-name mb-0">
                                            {{ $attachment->file_name }}
                                            <small class="text-muted ms-2">
                                                ({{ number_format($attachment->size / 1024, 2) }} KB)
                                            </small>
                                        </h6>
                                        <div class="attachment-actions">

                                            <!-- View Button -->
                                            <a title="View Attachment" data-toggle="tooltip"
                                                href="{{ $attachment->file_path }}"
                                                class="btn btn-outline-secondary btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- Download Button -->
                                            <a title="Download Attachment" data-toggle="tooltip"
                                                href="{{ $attachment->file_path }}"
                                                class="btn btn-outline-secondary btn-sm" download>
                                                <i class="fas fa-download"></i>
                                            </a>


                                            <!-- Delete Button -->
                                            @if ($deletePermission)
                                                <form id="deleteAttachment{{ $attachment->id }}" method="POST"
                                                    action="{{ route(getPanelRoutes('leads.attachment.destroy'), ['id' => $attachment->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button title="Delete Attachment" data-toggle="tooltip"
                                                        class="btn btn-danger btn-sm confirm-delete-attachment" type="submit"
                                                        data-id="{{ $attachment->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('.confirm-delete-attachment').forEach((button) => {
                button.addEventListener('click', (e) => {
                    e.preventDefault(); // Prevent form submission
                    const form = e.target.closest('form'); // Get the form element
                    const confirmation = confirm(
                    'Are you sure you want to delete this attachment?');
                    if (confirmation) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });


            const fileUploadAttachment = document.getElementById('fileUpload');
            const fileUploadArea = document.querySelector('.file-upload-area');
            const fileList = document.getElementById('fileList');
            let selectedFiles = []; // Array to keep track of selected files

            // Handle manual file selection
            fileUploadAttachment.addEventListener('change', function() {
                addFilesToList(this.files);
            });

            // Handle drag-and-drop events
            fileUploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                fileUploadArea.classList.add('drag-over');
            });

            fileUploadArea.addEventListener('dragleave', () => {
                fileUploadArea.classList.remove('drag-over');
            });

            fileUploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                fileUploadArea.classList.remove('drag-over');
                addFilesToList(e.dataTransfer.files);
            });

            // Add files to the selected list
            function addFilesToList(files) {
                for (let file of files) {
                    // Avoid adding duplicate files
                    if (!selectedFiles.some((f) => f.name === file.name && f.size === file.size)) {
                        selectedFiles.push(file);
                    }
                }
                renderFileList();
            }

            // Render the selected file list
            function renderFileList() {
                fileList.innerHTML = ''; // Clear existing list

                selectedFiles.forEach((file, index) => {
                    const listItem = document.createElement('li');
                    listItem.classList.add('file-item');
                    listItem.innerHTML = `
                <div>
                    <span class="file-name" title="${file.name}">${truncateFileName(file.name, 30)}</span>
                    <span class="file-size">(${(file.size / 1024).toFixed(2)} KB)</span>
                </div>
                <button class="remove-file-btn" data-file-index="${index}">
                    <i class="fas fa-trash"></i>
                </button>
            `;
                    fileList.appendChild(listItem);
                });

                // Attach event listeners to remove buttons
                document.querySelectorAll('.remove-file-btn').forEach((button) => {
                    button.addEventListener('click', removeFile);
                });
            }

            // Remove a file from the list
            function removeFile(event) {
                const index = event.target.closest('button').getAttribute('data-file-index');
                selectedFiles.splice(index, 1); // Remove the file from the array
                renderFileList(); // Re-render the list
            }

            // Truncate file names for better display
            function truncateFileName(name, maxLength) {
                return name.length > maxLength ? name.substring(0, maxLength - 3) + '...' : name;
            }
        });
    </script>
@endpush
