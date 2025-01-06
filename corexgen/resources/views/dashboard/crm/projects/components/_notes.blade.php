@php
    // prePrintR($comments);
@endphp

<div class="notes-section">
    <div class="mb-3">
        <form id="commentForm" method="POST" action="{{ route(getPanelRoutes('projects.comment.create')) }}">
            @csrf
            <input type="hidden" name="id" value="{{ $project->id }}" />
            <textarea name='comment' class="form-control wysiwyg-editor-comment" rows="3" placeholder="Add a note..."></textarea>
            <div class="d-flex justify-content-center my-2">
                <button form="commentForm" class="btn btn-primary mt-2" type="submit">Add Note</button>
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
        @if ($project?->comments?->count() > 0)
            @foreach ($project?->comments as $comment)
                <div class="comment-item mb-4">
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
                            <div
                                class="comment-header text-muted d-flex gap-3 justify-content-between align-items-center">
                                <h6 class="comment-author mb-0">
                                    {{ $comment->user->name }}
                                    <small class="text-muted ms-2">
                                        <i class="far fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($comment->created_at)->diffForHumans() }}
                                    </small>
                                </h6>
                                @if ($deletePermission)
                                    <form id="deleteComment{{ $comment->id }}" method="POST"
                                        action="{{ route(getPanelRoutes('projects.comment.destroy'), ['id' => $comment->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button title="Delete Comment" data-toggle="tooltip"
                                            class="btn btn-danger btn-sm confirm-delete-note" type="submit"
                                            form="deleteComment{{ $comment->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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


@push('scripts')
    <script>
        document.querySelectorAll('.confirm-delete-note').forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent form submission
                const form = e.target.closest('form'); // Get the form element
                const confirmation = confirm(
                    'Are you sure you want to delete this note?');
                if (confirmation) {
                    form.submit(); // Submit the form if confirmed
                }
            });
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
