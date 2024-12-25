@extends('layout.app')

@section('style')
    <style>
        .kanban-board {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            overflow-x: auto;
            min-height: calc(100vh - 200px);
            background: #f8fafc;
        }

        .kanban-column {
            min-width: 350px;
            background: #fff;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .kanban-column-header {
            padding: 1rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .column-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-count {
            background: #e2e8f0;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            color: #475569;
        }

        .kanban-card {
            background: white;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            cursor: move;
            transition: all 0.2s ease;
        }

        .kanban-card:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-company {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .card-type {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
        }

        .card-details {
            display: grid;
            gap: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .detail-item i {
            width: 16px;
            color: #94a3b8;
        }

        .card-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-date {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .card-action-btn {
            padding: 0.25rem;
            border-radius: 0.375rem;
            color: #64748b;
            transition: all 0.2s;
        }

        .card-action-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .stage-indicator {
            width: 3px;
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            border-radius: 9999px;
        }

        .dragging {
            opacity: 0.6;
            transform: scale(1.02);
        }

        /* Custom scrollbar */
        .kanban-board::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-board::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .kanban-board::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
@endsection

@section('content')
    @php
      //  prePrintR($stages->toArray());
        // die();
    @endphp
    <div class="container-fluid">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Leads Pipeline</h4>

            </div>
        </div>

        @if (hasPermission('LEADS.READ_ALL') || hasPermission('LEADS.READ'))
            <div class="kanban-board" id="kanbanBoard">
                @foreach ($stages as $stage)
                    <div class="kanban-column" data-stage-id="{{ $stage->id }}" ondrop="drop(event)"
                        ondragover="allowDrop(event)">
                        <div class="kanban-column-header">
                            <div class="column-title">
                                <i class="fas fa-{{ $stage->icon ?? 'circle' }}"></i>
                                {{ $stage->name }}
                                <span class="card-count">{{ $stage->leads_count }}</span>
                            </div>
                        </div>

                        @foreach ($stage->leads as $lead)
                            <div class="kanban-card position-relative" draggable="true" ondragstart="drag(event)"
                                data-lead-id="{{ $lead->id }}">
                                <div class="stage-indicator" style="background-color: {{ $stage->color ?? '#94a3b8' }}">
                                </div>

                                <div class="card-header">
                                    <div>
                                        <div class="card-company">{{ $lead->company_name }}</div>
                                        <span class="card-type">{{ $lead->type }}</span>
                                    </div>
                                </div>

                                <div class="card-details">
                                    <div class="detail-item">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $lead->title }} {{ $lead->first_name }} {{ $lead->last_name }}</span>
                                    </div>

                                    @if ($lead->email)
                                        <div class="detail-item">
                                            <i class="fas fa-envelope"></i>
                                            <span>{{ $lead->email }}</span>
                                        </div>
                                    @endif

                                    @if ($lead->phone)
                                        <div class="detail-item">
                                            <i class="fas fa-phone"></i>
                                            <span>{{ $lead->phone }}</span>
                                        </div>
                                    @endif

                                    @if ($lead->address)
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>{{ $lead->address }}</span>
                                        </div>
                                    @endif

                                    @if ($lead->group)
                                        <div class="detail-item">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $lead->group->name }}</span>
                                        </div>
                                    @endif

                                    @if ($lead->source)
                                        <div class="detail-item">
                                            <i class="fas fa-tag"></i>
                                            <span>{{ $lead->source->name }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="card-footer">
                                    <div class="card-date">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $lead->created_at->diffForHumans() }}
                                    </div>
                                    <div class="card-actions">
                                        {{-- <a href="{{ route('leads.edit', $lead->id) }}" class="card-action-btn">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="{{ route('leads.show', $lead->id) }}" class="card-action-btn">
                                <i class="fas fa-eye"></i>
                            </a> --}}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ __('crud.You do not have permission to view the board') }}
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            const card = ev.target.closest('.kanban-card');
            card.classList.add('dragging');
            ev.dataTransfer.setData("leadId", card.dataset.leadId);
        }

        function drop(ev) {
            ev.preventDefault();
            const card = document.querySelector('.dragging');
            card.classList.remove('dragging');

            const leadId = ev.dataTransfer.getData("leadId");
            const newStageId = ev.target.closest('.kanban-column').dataset.stageId;

            fetch(`/`, {
                    // leads.updateStage
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        lead_id: leadId,
                        stage_id: newStageId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const targetColumn = ev.target.closest('.kanban-column');
                        const header = targetColumn.querySelector('.kanban-column-header');
                        header.after(card);
                        updateColumnCounts();

                        // Show success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Stage Updated',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
        }

        function updateColumnCounts() {
            document.querySelectorAll('.kanban-column').forEach(column => {
                const count = column.querySelectorAll('.kanban-card').length;
                column.querySelector('.card-count').textContent = count;
            });
        }
    </script>
@endsection
