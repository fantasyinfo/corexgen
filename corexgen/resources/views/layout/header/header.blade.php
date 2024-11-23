<!DOCTYPE html>
<html lang="{{config('app.locale') || 'en'}}">
  <head>
    
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="{{ config('app.name') }}" />


    <title>{{$title ? $title : ''}} || {{ config('app.name') }}</title>


    @include('layout.header.css-links')
    @stack('style')
  </head>