@component('mail::message')
# Hello {{ $estimate->typable->first_name . ' ' . $estimate->typable->last_name }}

A new estimate has been sent to you. Please find the attachment below as a PDF.

@component('mail::button', ['url' => url('/estimate/view/' . $estimate->uuid)])
View Estimate
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
