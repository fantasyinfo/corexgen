@component('mail::message')
# Hello {{ $invoice?->client?->first_name . ' ' . $invoice?->typable?->last_name }}

A new invoice has been sent to you. Please find the attachment below as a PDF.

@component('mail::button', ['url' => url('/invoices/view/' . $invoice->id)])
View Invoice
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
