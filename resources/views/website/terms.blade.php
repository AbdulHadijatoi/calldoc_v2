@extends('layout.mainlayout',['activePage' => 'terms'])

@section('content')

<div class="pt-14">
    <h1 class="font-fira-sans font-semibold text-5xl text-center leading-10 mb-5">{{__('Terms of Service')}}</h1>
    <div class="xsm:mx-20 xxsm:mx-5">
        <div class="mb-10">
            {!! $terms !!}
            
        </div>
    </div>
</div>


@endsection
