<!DOCTYPE html>
<html lang="{{config('app.locale') || 'en'}}">

{{-- head --}}
@include('layout.header.head')

<style>
    body.modal-open .nxl-container{
        filter:none !important;
    }
</style>
<body>

{{-- nav --}}
@include('layout.header.nav')

{{-- top --}}
@include('layout.header.top')
<main class="nxl-container">

