@extends('layout.mainlayout',['activePage' => 'doctors-information'])

@section('title',__('Doctors Information'))

@section('content')

    <div id="app">
        <doctors/>
    </div>

    @vite('resources/js/app.js')
@endsection
