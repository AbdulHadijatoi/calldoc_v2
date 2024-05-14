<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>


        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">

        {{-- <div id="app"></div> --}}

        @vite('resources/js/app.js')
        <script>
            window.selectedComponent = "component2";
        </script>
    </body>
</html>
