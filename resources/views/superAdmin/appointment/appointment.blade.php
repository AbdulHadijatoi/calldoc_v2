@extends('layout.mainlayout_admin',['activePage' => 'appointment'])

@section('title',__('Appointment'))

@section('content')

    <section class="section">
        @include('layout.breadcrumb',[
            'title' => __('Appointment'),
        ])

        <div id="app">
            <appointments :type="'{{ $type }}'"/>
        </div>

    </section>
    @vite('resources/js/app.js')
@endsection
