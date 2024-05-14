@extends('layout.mainlayout',['activePage' => 'ourblogs'])

@section('content')

{{-- <div class="mt-20 border-b border-white-light ">
    <h1 class="font-fira-sans font-semibold text-5xl text-center leading-10">{{__('Our Blogs ')}}</h1>
    <div class="p-5">
        <p class="font-fira-sans font-normal text-lg text-center leading-5 text-gray">{{__('Lorem ipsum dolor sit
            amet, consectetur adipiscing elit.')}}</p>
    </div>
</div> --}}

{{-- Ayurveda For Prostate --}}
<div class="xsm:mx-5 xlg:mx-20 pb-6">
    <div class="">
        <div class="pt-10">
            <h1 class="text-center font-fira-sans text-black font-medium text-4xl">{{ $blog->title }}</h1>
            <p class="py-5 font-fira-sans font-medium text-base text-center leading-5 text-blue">{{ $blog->blog_ref }}
                <span class="text-gray font-normal leading-5">• {{ Carbon\Carbon::parse($blog->created_at)->format('d M,Y') }}</span>
            </p>
        </div>
    </div>
</div>
{{-- full image --}}
<div class="mb-10">
        <div class="flex justify-center mb-10">
            <img src="{{asset($blog->fullImage)}}" class="w-[67%] object-fill xxsm:h-[200px] xsm:h-[300px] sm:h-[400px] xxmd:h-[500px] lg:h-[700px]" alt="Logo">
        </div>

        <div class="w-2/3 mx-auto">
            <div class="mx-auto">
                {{ strip_tags(html_entity_decode($blog->desc)) }}
            </div>
        </div>
    </div>
@endsection
