@extends('layout.mainlayout_admin',['activePage' => 'lab'])

@section('title',__('Laboratory'))

@section('content')
<section class="section">
    @include('layout.breadcrumb',[
        'title' => __('Laboratory'),
    ])
    <div class="section_body">
        @if (session('status'))
        @include('superAdmin.auth.status',[
            'status' => session('status')])
        @endif
        <div class="card">
            <div class="card-header w-100 text-right d-flex justify-content-between">
                @include('superAdmin.auth.exportButtons')
                @can('lab_add')
                    <a href="{{  url('laboratory/create') }}">{{ __('Add New') }}</a>                
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <input name="select_all" value="1" id="master" type="checkbox" />
                                    <label for="master"></label>
                                </th>
                                <th>#</th>
                                <th>{{__('Laboratory Image')}}</th>
                                <th>{{__('Laboratory Name')}}</th>
                                <th>{{__('Pathologist Name')}}</th>
                                <th>{{__('Pathologist email')}}</th>
                                <th>{{__('Status')}}</th>
                                @if (Gate::check('lab_edit') || Gate::check('lab_delete'))
                                    <th>{{__('Actions')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($labs as $lab)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="id[]" value="{{$lab->id}}" id="{{$lab->id}}" data-id="{{ $lab->id }}" class="sub_chk">
                                        <label for="{{$lab->id}}"></label>
                                    </td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <img class="avatar-img rounded-circle" width="50px" height="50px" src="{{ $lab->fullImage }}" alt="doctor Image"></a>
                                    </td>
                                    <td>{{ $lab->name }}</td>
                                    <td>{{ $lab->user['name'] }}</td>
                                    <td>
                                        <a href="mailto:{{$lab->user['email']}}">
                                            <span class="text_transform_none">{{ $lab->user['email'] }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <label class="cursor-pointer">
                                            <input type="checkbox"id="status_1{{$lab->id}}" class="custom-switch-input" onchange="change_status('lab',{{ $lab->id }})" {{ $lab->status == 1 ? 'checked' : '' }}>
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    </td>
                                    @if (Gate::check('lab_edit') || Gate::check('lab_delete'))
                                        <td>
                                            @can('lab_edit')
                                            <a class="text-success" href="{{url('laboratory/'.$lab->id.'/edit')}}">
                                                <i class="far fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('lab_delete')
                                            <a class="text-danger" href="javascript:void(0);" onclick="deleteData('laboratory',{{ $lab->id }})">
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
            <div class="card_fotter">
                <input type="button" value="delete selected" onclick="deleteAll('lab_all_delete')" class="btn btn-primary">
            </div>
        </div>
    </div>
</section>

@endsection