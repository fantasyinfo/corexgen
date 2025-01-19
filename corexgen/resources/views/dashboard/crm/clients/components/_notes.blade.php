@php
    // prePrintR($comments);
@endphp


@push('style')
    <style>
        .comments-section {
            /* background-color: #fff; */
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .comments-title {
            color: var(--body-color);
            font-weight: 600;
        }

        .comment-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .comment-avatar img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .default-avatar {
            width: 48px;
            height: 48px;
            /* background-color: #e9ecef; */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--body-color);
        }

        .comment-author {
            /* color: var(--body-color); */
            font-weight: 600;
        }

        .comment-body {
            color: var(--body-color);
            line-height: 1.6;
        }

        .no-comments {
            color: var(--body-color);
        }

        /* Hover effect for comment items */
        .comment-item:hover {
            /* background-color: #f8f9fa; */
            transition: background-color 0.2s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {

            .comment-avatar img,
            .default-avatar {
                width: 40px;
                height: 40px;
            }

            .comments-section {
                padding: 15px;
            }
        }
    </style>
@endpush

<div class="notes-section">
    <div class="mb-3">
        <form id="commentForm" method="POST" action="{{ route(getPanelRoutes('clients.comment.create')) }}">
            @csrf
            <input type="hidden" name="id" value="{{ $client->id }}" />
            <textarea name="comment" class="form-control wysiwyg-editor-comment" rows="3" placeholder="Add a note..."></textarea>
            <div class="d-flex justify-content-center my-2">
                <button class="btn btn-primary mt-2" type="submit">Add Note</button>
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
        @if ($client?->comments?->count() > 0)
            @foreach ($client->comments as $comment)
                <div class="comment-item mb-4" data-id="{{ $comment->id }}">
                    <div class="d-flex">
                        <!-- User Avatar -->
                        <div class="comment-avatar">
                            @if ($comment->user->profile_photo_path)
                                <x-form-components.profile-avatar :src="$comment->user->profile_photo_path" />
                            @else
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Comment Content -->
                        <div class="comment-content flex-grow-1 ms-3">
                            <div class="comment-header text-muted d-flex justify-content-between align-items-center">
                                <h6 class="comment-author mb-0">
                                    {{ $comment->user->name }}
                                    <small class="text-muted ms-2">
                                        <i class="far fa-clock"></i> {{ $comment->created_at->diffForHumans() }}
                                    </small>
                                </h6>
                                @if ($deletePermission)
                                    <div class="action-buttons">

                                        <button class="btn btn-danger btn-sm delete-comment"
                                            data-id="{{ $comment->id }}"
                                            data-url="{{ route(getPanelRoutes('clients.comment.destroy'), ['id' => $comment->id]) }}">Delete</button>
                                    </div>
                                @endif
                            </div>
                            <div class="comment-body mt-2">
                                <p class="mb-0">{!! $comment->comment !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="no-comments text-center py-4">
                <i class="far fa-comment-dots fa-2x text-muted"></i>
                <p class="mt-2 text-muted">No comments yet</p>
            </div>
        @endif
    </div>
</div>




@push('scripts')
    <script>
        $('#commentForm').submit(function(e) {
            e.preventDefault();

            if (typeof tinymce !== 'undefined' && tinymce.get('comment')) {
                const content = tinymce.get('comment').getContent();
                if (!content.trim()) {
                    alert('Please enter a comment');
                    return;
                }
                $('textarea[name="comment"]').val(content);
            }


            const formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    let deleteURL =
                        "{{ route(getPanelRoutes('clients.comment.destroy'), ['id' => ':id']) }}";
                    deleteURL = deleteURL.replace(':id', response.comment.id);

                    let isDeletePermission =
                        "{{ hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY']) }}";

                    // console.log(isDeletePermission)
                    // Add the new comment to the UI
                    const deleteButton = isDeletePermission ?
                        `<div class="action-buttons">
                                    <button class="btn btn-danger btn-sm delete-comment" data-id="${response.comment.id}" data-url="${deleteURL}">Delete</button>
                            </div>` :
                        '';

                    $('.note-list').prepend(`
    <div class="comment-item mb-4" data-id="${response.comment.id}">
        <div class="d-flex">
            <div class="comment-avatar">
                ${response.user.profile_photo_path ? 
                    `<img src="${response.user.profile_photo_path}" alt="Avatar" class="rounded-circle">` : 
                    `<div class="default-avatar"><i class="fas fa-user"></i></div>`}
            </div>
            <div class="comment-content flex-grow-1 ms-3">
                <div class="comment-header text-muted d-flex justify-content-between align-items-center">
                    <h6 class="comment-author mb-0">
                        ${response.user.name}
                        <small class="text-muted ms-2"><i class="far fa-clock"></i> Just now</small>
                    </h6>
                    ${deleteButton}
                </div>
                <div class="comment-body mt-2">
                    <p class="mb-0">${response.comment.comment}</p>
                </div>
            </div>
        </div>
    </div>
`);


                    // Clear the form
                    $('#commentForm')[0].reset();
                    alert('Comment added successfully', 'success');
                },
                error: function(xhr) {
                    alert('An error occurred while adding the comment.');
                },
            });
        });

        $(document).on('click', '.delete-comment', function() {
            const button = $(this);
            const url = button.data('url');

            if (confirm('Are you sure you want to delete this comment?')) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    },
                    success: function() {
                        button.closest('.comment-item').remove();
                        alert('Comment deleted successfully', 'success');
                    },
                    error: function() {
                        alert('An error occurred while deleting the comment.');
                    },
                });
            }
        });

        // Initialize WYSIWYG editor
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '.wysiwyg-editor-comment',
                height: 300,
                base_url: '/js/tinymce',
                license_key: 'gpl',
                skin: currentTheme === 'dark' ? 'oxide-dark' : 'oxide',
                content_css: currentTheme === 'dark' ? 'dark' : 'default',
                menubar: false,
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save(); // This automatically updates the textarea
                    });
                },
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
                toolbar: 'undo redo | formatselect | bold italic backcolor | \alignleft aligncenter alignright alignjustify | \bullist numlist outdent indent | removeformat | help | \link image media preview codesample table code'
            });
        }
    </script>
@endpush
