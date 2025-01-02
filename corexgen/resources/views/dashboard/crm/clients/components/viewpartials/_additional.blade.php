@php
    //  prePrintR($client->toArray());
@endphp
<div class="col-md-6">
    <h6 class="detail-label">Additional Information</h6>
    <div class="detail-group">
        <label>Category</label>
        <p>{{ $client?->categoryGroupTag?->name ?? 'N/A' }}</p>
    </div>
    <div class="detail-group">
        <label>Tags</label>
        <div>
            @if (!empty($client->tags) && is_array($client->tags))
                @foreach ($client->tags as $tag)
                    <span class="badge bg-primary me-1">{{ $tag }}</span>
                @endforeach
            @else
                <p>N/A</p>
            @endif
        </div>
    </div>
    <div class="detail-group">
        <label>Social Media</label>
        @if (!empty($client?->social_media))
            @foreach ($client->social_media as $platform => $url)
                @php
                    $platform = trim($platform, '\'');
                @endphp
                <div class="mb-2">
                    <a href="{{ $url }}" class="badge text-decoration-none d-inline-block py-2 px-3" 
                       style="color: white; background-color: 
                       @switch($platform)
                           @case('x') #1DA1F2; @break /* Twitter Blue */
                           @case('fb') #3b5998; @break /* Facebook Blue */
                           @case('in') #C13584; @break /* Instagram Violet-Pink */
                           @case('ln') #0077B5; @break /* LinkedIn Blue */
                           @default #6c757d; /* Default Gray */
                       @endswitch" 
                       target="_blank" rel="noopener noreferrer">
                        @switch($platform)
                            @case('x')
                                <i class="fas fa-twitter"></i> Twitter
                                @break
                            @case('fb')
                                <i class="fas fa-facebook"></i> Facebook
                                @break
                            @case('in')
                                <i class="fas fa-instagram"></i> Instagram
                                @break
                            @case('ln')
                                <i class="fas fa-linkedin"></i> LinkedIn
                                @break
                            @default
                                <i class="fas fa-link"></i> {{ ucfirst($platform) }}
                        @endswitch
                    </a>
                </div>
            @endforeach
        @else
            <p>N/A</p>
        @endif
    </div>
    
    


</div>
