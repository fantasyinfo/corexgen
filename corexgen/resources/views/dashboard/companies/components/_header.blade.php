<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1 class="mb-1"> {{ $company?->name }} </h1>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-info">
                        <i class="fas fa-flag me-1"></i> {{ $company?->plans?->name }}
                    </span>
                </div>
            </div>
        </div>
    </div>
 
</div>
