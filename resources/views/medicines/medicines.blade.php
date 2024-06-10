@extends('layout.mainlayout',['activePage' => 'medicines'])

@section('title',__('Medicines'))

@section('content')

    <div id="app">
        <medicine/>
    </div>

    @vite('resources/js/app.js')
@endsection
