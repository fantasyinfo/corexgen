@component('mail::message')
    # Hi, {{ $proposal->typable->first_name . ' ' . $proposal->typable->last_name }}

    A new proposal has been sent to you. Please find the attachment below as a PDF.

    @component('mail::button', ['url' => url('/proposal/view/' . $proposal->id)])
    {{ __('View Proposal') }}
    @endcomponent

    Thanks,
    {{ config('app.name') }}
@endcomponent
