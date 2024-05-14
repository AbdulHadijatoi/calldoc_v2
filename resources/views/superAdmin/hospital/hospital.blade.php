@extends('layout.mainlayout_admin',['activePage' => 'hospital'])

@section('title',__('All Hospital'))
@section('content')

<section class="section">
    @include('layout.breadcrumb',[
        'title' => __('hospital'),
    ])
    <div class="section_body">
        @if (session('status'))
            @include('superAdmin.auth.status',['status' => session('status')])
        @endif
        <div class="card">
            <div class="card-header w-100 text-right d-flex justify-content-between">
                @include('superAdmin.auth.exportButtons')
                @can('hospital_add')
                    <a href="{{ url('hospital/create') }}">{{ __('Add New') }}</a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="w-100 display text-center table datatable">
                        <thead>
                            <tr>
                                <th>
                                    <input name="select_all" value="1" id="master" type="checkbox" />
                                    <label for="master"></label>
                                </th>
                                <th> # </th>
                                <th>{{__('Name')}}</th>
    
                                <th>{{__('Status')}}</th>
                                @if (Gate::check('hospital_edit') || Gate::check('hospital_delete'))
                                    <th> {{__('Action')}} </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hospitals as $hospital)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="id[]" value="{{$hospital->id}}" id="{{$hospital->id}}" data-id="{{ $hospital->id }}" class="sub_chk">
                                        <label for="{{$hospital->id}}"></label>
                                    </td>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$hospital->name}}</td>
                                    <td>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" id="status{{$hospital->id}}" class="custom-switch-input" name="status" onchange="change_status('hospital',{{ $hospital->id }})" {{ $hospital->status == 1 ? 'checked' : '' }}>
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    </td>
                                    @if (Gate::check('hospital_edit') || Gate::check('hospital_delete'))
                                        <td>
                                            @can('hospital_edit')
                                            <a class="text-success" href="{{url('hospital/'.$hospital->id.'/edit/')}}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('hospital_delete')
                                            <a class="text-danger"  href="javascript:void(0);" onclick="deleteData('hospital',{{ $hospital->id }})">
                                                <i class="far fa-trash-alt"></i>
                                            </a>
                                            @endcan
                                            @can('hospital_gallery_access')
                                                <a href="{{ url('hospitalGallery/'.$hospital->id) }}" class="btn btn-sm bg-primary-light ml-1">
                                                    <i class="fe fe-plus"></i> {{('Hospital gallery')}}
                                                </a>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <input type="button" value="delete selected" onclick="deleteAll('hospital_all_delete')" class="btn btn-primary">
            </div>
        </div>
    </div>
</section>

@endsection
