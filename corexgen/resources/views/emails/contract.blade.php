@component('mail::message')
# Hello {{ $contract->typable->first_name . ' ' . $contract->typable->last_name }}

A new contract has been sent to you. Please find the attachment below as a PDF.

@component('mail::button', ['url' => url('/contract/view/' . $contract->id)])
View Contract
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
