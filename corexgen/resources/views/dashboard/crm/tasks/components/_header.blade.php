<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1 class="mb-1">
                    {{$task->title}}
                </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $task->priority }} Priority
                    </span>
                    <span class="badge bg-{{ $task->stage->color }}">
                        {{ $task->stage->name }}
                    </span>
                    @if ($task->billable)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Billable
                        </span>
                    @endif
                   
                </div>
            </div>
        </div>
    </div>
 
</div>
