<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Lumina ERP') }}</title>


        @inertiaHead


    </head>
    <body class="font-sans antialiased">
        @inertia

        @viteReactRefresh
        @vite(['resources/js/app.jsx'])
    </body>
</html>
