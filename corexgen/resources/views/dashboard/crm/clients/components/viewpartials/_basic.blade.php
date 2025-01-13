<div class="col-md-6">
    <h6 class="detail-label">Basic Information</h6>
    <div class="detail-group">
        <label>Type</label>
        <p>{{ $client->type }}</p>
    </div>
    <div class="detail-group">
        <label>Company Name</label>
        <p>{{ $client->company_name }}</p>
    </div>
    <div class="detail-group">
        <label>Title</label>
        <p>{{ $client->title }}</p>
    </div>
    <div class="detail-group">
        <label>First Name</label>
        <p>{{ $client->first_name }}</p>
    </div>
    <div class="detail-group">
        <label>Last Name</label>
        <p>{{ $client->last_name }}</p>
    </div>
    <div class="detail-group">
        <label>Email</label>
        <div>
            @if (!empty($client->email) && is_array($client->email))
                @foreach ($client->email as $email)
                    <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                @endforeach
            @else
                <p>N/A</p>
            @endif
        </div>
    </div>

    <div class="detail-group">
        <label>Phone</label>
        <div>
            @if (!empty($client->phone) && is_array($client->phone))
                @foreach ($client->phone as $phone)
                    <p><a href="tel:{{ $phone }}">{{ $phone }}</a></p>
                @endforeach
            @else
                <p>N/A</p>
            @endif
        </div>
    </div>

</div>
