<div id="task-detail-modal" class="task-modal hidden">
    <div class="modal-content">
        <button class="close-btn" onclick="closeTaskModal()">×</button>
        <h3>{{ $task->title }}</h3>
        <p>{{ $task->description }}</p>
        <div class="meta-info">
            <p><strong>Status:</strong> {{ $task->status }}</p>
            <p><strong>Assigned to:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</p>
            <!-- Add additional task details here -->
        </div>
        <div class="actions">
            <button class="btn btn-primary" onclick="editTask({{ $task->id }})">Edit</button>
            <button class="btn btn-danger" onclick="deleteTask({{ $task->id }})">Delete</button>
        </div>
    </div>
</div>


@push('style')
    <style>
        .task-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .task-modal .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            max-width: 600px;
            width: 100%;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .task-modal .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        .task-modal.visible {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
@endpush
