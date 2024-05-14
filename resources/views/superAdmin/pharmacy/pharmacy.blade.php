@extends('layout.mainlayout_admin',['activePage' => 'pharmacy'])

@section('title',__('All pharmacy'))
@section('content')

<section class="section">
    @include('layout.breadcrumb',[
        'title' => __('Pharmacy'),
    ])
    @if (session('status'))
        @include('superAdmin.auth.status',[
            'status' => session('status')])
        @endif
        <div class="card">
            <div class="card-header w-100 text-right d-flex justify-content-between">
                @include('superAdmin.auth.exportButtons')
                @can('pharmacy_add')
                    <a href="{{url('pharmacy/create')}}">{{__('Add New')}}</a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="w-100 display table datatable text-center">
                        <thead>
                            <tr>
                                <th>
                                    <input name="select_all" value="1" id="master" type="checkbox" />
                                    <label for="master"></label>
                                </th>
                                <th> # </th>
                                <th>{{__('Image')}}</th>
                                <th>{{__('Pharmacy name')}}</th>
                                <th>{{__('email')}}</th>
                                <th>{{__('phone')}}</th>
                                <th>{{__('status')}}</th>
                                @if (Gate::check('pharmacy_edit') || Gate::check('pharmacy_delete'))
                                    <th> {{__('Action')}} </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pharamacies as $pharmacy)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="id[]" value="{{$pharmacy->id}}" id="{{$pharmacy->id}}" data-id="{{ $pharmacy->id }}" class="sub_chk">
                                        <label for="{{$pharmacy->id}}"></label>
                                    </td>
                                    <td>{{$loop->iteration}}</td>
                                    <td>
                                        <a href="{{ $pharmacy->fullImage }}" data-fancybox="gallery2">
                                            <img class="avatar-img rounded-circle" alt="Pharamcy Image" src="{{ $pharmacy->fullImage }}" height="50" width="50">
                                        </a>
                                    </td>
                                    <td>{{$pharmacy->name}}</td>
                                    <td>
                                        <span class="text_transform_none">{{$pharmacy->email}}</span>
                                    </td>
                                    <td>{{$pharmacy->phone}}</td>

                                    <td>
                                        <label class="cursor-pointer">
                                            <input type="checkbox" id="status{{$pharmacy->id}}" class="custom-switch-input" name="status" onchange="change_status('pharmacy',{{ $pharmacy->id }})" {{ $pharmacy->status == 1 ? 'checked' : '' }}>
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    </td>
                                    @if (Gate::check('pharmacy_edit') || Gate::check('pharmacy_delete'))
                                        <td>
                                            <a class="text-primary" href="{{url('pharmacy/'.$pharmacy->id)}}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            @can('pharmacy_edit')
                                            <a class="text-success" href="{{url('pharmacy/'.$pharmacy->id.'/edit/')}}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('pharmacy_delete')
                                                <a class="text-danger" href="javascript:void(0);"  onclick="deleteData('pharmacy',{{ $pharmacy->id }})">
                                                    <i class="far fa-trash-alt"></i>
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
            <div class="card-fotter">
                <input type="button" value="delete selected" onclick="deleteAll('pharmacy_all_delete')" class="btn btn-primary">
            </div>
        </div>
</section>

@endsection
