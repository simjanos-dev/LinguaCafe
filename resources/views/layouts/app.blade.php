<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LangApp</title>

    <script src="{{ mix('js/app.js') }}" defer></script>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('header')
    <link href="{{ asset('css/awesomefont.css') }}" rel="stylesheet">
    @if (isset($_COOKIE["ebook-reader-mode"]))
        <link href="{{ asset('css/ebook_reader_mode.css') }}" rel="stylesheet">
    @endif
</head>
<body><!--
--><div id="app"><!--
    -->@include('layouts.navbar')<!--
    -->@yield('content')<!--
--></div><!--
--></body></html>
