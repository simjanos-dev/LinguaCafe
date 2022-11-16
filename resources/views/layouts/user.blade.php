<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7b4cfd" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LangApp</title>

    <script src="{{ mix('js/app.js') }}" defer></script>
    <script src="/js/dmak/raphael.js"></script>
    <script src="/js/dmak/dmak.js"></script>
    <script src="/js/dmak/dmakLoader.js"></script>
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
    --><layout-component></layout-component><!--
--></div><!--
--></body></html>
