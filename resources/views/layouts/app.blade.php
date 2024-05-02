<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="./manifest.json"> 
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LinguaCafe</title>

    <script src="{{ mix('js/app.js') }}" defer></script>
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('header')
</head>
<body>
<div id="app">
    @yield('content')
</div>
</body></html>
