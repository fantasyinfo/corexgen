<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="d-flex align-items-center gap-3">
            <div class="lead-avatar">
                @if ($client->type == 'Company')
                    <div class="company-avatar">{{ substr($client->company_name, 0, 2) }}</div>
                @else
                    <div class="individual-avatar">
                        {{ substr($client->first_name, 0, 1) }}{{ substr($client->last_name, 0, 1) }}</div>
                @endif
            </div>
            <div>
                <h1 class="mb-1">
                    @if ($client->type == 'Company')
                        {{ $client->company_name }}
                    @else
                        {{ $client->first_name }} {{ $client->last_name }}
                    @endif
                </h1>
          
            </div>
        </div>
    </div>
 
</div>
