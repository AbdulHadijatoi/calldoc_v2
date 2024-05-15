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
        @vite('resources/js/app.js')
    </head>
    <body class="antialiased">

        <h1>Calendar Index Blade View2</h1>
        <div id="app">
            <component />
        </div>

        
    </body>
</html>
